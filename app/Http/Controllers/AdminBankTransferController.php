<?php

namespace App\Http\Controllers;

use App\Models\BankImportProfile;
use App\Models\BankTransferAudit;
use App\Models\EventCategory;
use App\Models\EventAddon;
use App\Models\EventCategoryPriceTier;
use App\Models\Order;
use App\Models\Setting;
use App\Services\BankReconciliationService;
use App\Services\OrderPaymentFinalizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AdminBankTransferController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::with(['tickets', 'bankTransactions', 'transferAudits.user'])
            ->where('payment_method', 'bank_transfer')
            ->when($request->status, fn ($query, $status) => $query->where('status', $status))
            ->latest()
            ->paginate(30);
        $settings = Setting::whereIn('key', [
            'bank_transfer_enabled', 'bank_account_holder', 'bank_iban', 'bank_bic', 'bank_name',
            'event_date', 'bank_transfer_cutoff_days', 'bank_transfer_reservation_days',
        ])->pluck('value', 'key');

        return view('admin.bank-transfers', [
            'orders' => $orders, 'settings' => $settings,
            'profiles' => BankImportProfile::orderByDesc('is_default')->get(),
            'categories' => EventCategory::orderBy('sort_order')->get(),
            'shuttleAddon' => EventAddon::where('code', 'shuttle')->first(),
        ]);
    }

    public function updateSettings(Request $request)
    {
        $data = $request->validate([
            'bank_transfer_enabled' => 'nullable|boolean',
            'bank_account_holder' => 'nullable|required_if:bank_transfer_enabled,1|string|max:70',
            'bank_iban' => ['nullable', 'required_if:bank_transfer_enabled,1', 'string', 'max:34', 'regex:/^[A-Za-z]{2}[0-9A-Za-z ]{13,32}$/'],
            'bank_bic' => 'nullable|string|max:11', 'bank_name' => 'nullable|string|max:100',
            'event_date' => 'required|date', 'bank_transfer_cutoff_days' => 'required|integer|min:1|max:30',
            'bank_transfer_reservation_days' => 'required|integer|min:1|max:10',
            'categories' => 'required|array', 'categories.*.price' => 'required|numeric|min:0',
            'categories.*.capacity' => 'nullable|integer|min:1',
            'shuttle_addon_price' => 'required|numeric|min:0',
        ]);

        foreach (['bank_transfer_enabled', 'bank_account_holder', 'bank_iban', 'bank_bic', 'bank_name', 'event_date', 'bank_transfer_cutoff_days', 'bank_transfer_reservation_days'] as $key) {
            $value = $key === 'bank_transfer_enabled' ? ($request->boolean($key) ? '1' : '0') : ($data[$key] ?? '');
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
        foreach ($data['categories'] as $id => $categoryData) {
            EventCategory::whereKey($id)->update($categoryData);
            EventCategoryPriceTier::where('event_category_id', $id)
                ->whereNull('starts_at')->whereNull('ends_at')
                ->update(['price' => $categoryData['price']]);
        }
        EventAddon::where('code', 'shuttle')->update(['price' => $data['shuttle_addon_price']]);
        return back()->with('success', 'Configurazione bonifico e capienze aggiornata.');
    }

    public function storeProfile(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100', 'delimiter' => 'required|in:;,',
            'date_format' => 'required|string|max:20', 'date_column' => 'required|string|max:100',
            'amount_column' => 'required|string|max:100', 'description_column' => 'required|string|max:100',
            'external_id_column' => 'nullable|string|max:100', 'currency_column' => 'nullable|string|max:100',
        ]);
        BankImportProfile::create([
            'name' => $data['name'], 'delimiter' => $data['delimiter'], 'date_format' => $data['date_format'],
            'mapping' => ['date' => $data['date_column'], 'amount' => $data['amount_column'], 'description' => $data['description_column'], 'external_id' => $data['external_id_column'] ?: null, 'currency' => $data['currency_column'] ?: null],
        ]);
        return back()->with('success', 'Profilo CSV salvato.');
    }

    public function preview(Request $request, BankReconciliationService $service)
    {
        $data = $request->validate(['profile_id' => 'required|exists:bank_import_profiles,id', 'csv_file' => 'required|file|mimes:csv,txt|max:5120']);
        $profile = BankImportProfile::findOrFail($data['profile_id']);
        $path = $data['csv_file']->store('bank-imports', 'local');
        return view('admin.bank-import-preview', ['profile' => $profile, 'path' => $path, 'preview' => $service->preview(Storage::disk('local')->path($path), $profile)]);
    }

    public function confirmImport(Request $request, BankReconciliationService $service)
    {
        $data = $request->validate(['profile_id' => 'required|exists:bank_import_profiles,id', 'path' => ['required', 'string', 'starts_with:bank-imports/']]);
        abort_unless(Storage::disk('local')->exists($data['path']), 404);
        $stats = $service->import(Storage::disk('local')->path($data['path']), BankImportProfile::findOrFail($data['profile_id']));
        Storage::disk('local')->delete($data['path']);
        return redirect()->route('admin.bank.index')->with('success', 'Import completato: '.json_encode($stats));
    }

    public function approve(Request $request, Order $order)
    {
        $this->ensureBankOrder($order);
        $reason = $request->validate(['reason' => 'required|string|max:500'])['reason'];
        DB::transaction(function () use ($request, $order, $reason) {
            $order = Order::lockForUpdate()->findOrFail($order->id);
            $order->update(['transfer_verified_at' => now(), 'transfer_verification_method' => 'admin_manual', 'transfer_verified_by' => $request->user()->id, 'transfer_review_reason' => null]);
            app(OrderPaymentFinalizer::class)->finalize($order);
            BankTransferAudit::create(['order_id' => $order->id, 'user_id' => $request->user()->id, 'action' => 'manual_approved', 'reason' => $reason]);
        });
        return back()->with('success', 'Pagamento confermato e biglietti attivati.');
    }

    public function reject(Request $request, Order $order)
    {
        $this->ensureBankOrder($order);
        abort_if(in_array($order->status, ['paid', 'cancelled'], true), 409);
        $reason = $request->validate(['reason' => 'required|string|max:500'])['reason'];
        $status = $order->payment_due_at?->isFuture() ? 'awaiting_transfer' : 'expired';
        $order->update(['status' => $status, 'transfer_review_reason' => $reason]);
        BankTransferAudit::create(['order_id' => $order->id, 'user_id' => $request->user()->id, 'action' => 'manual_rejected', 'reason' => $reason]);
        return back()->with('success', 'Verifica rifiutata.');
    }

    public function cancel(Request $request, Order $order)
    {
        $this->ensureBankOrder($order);
        abort_if($order->status === 'paid', 409);
        $reason = $request->validate(['reason' => 'required|string|max:500'])['reason'];
        $order->update(['status' => 'cancelled', 'transfer_review_reason' => $reason]);
        BankTransferAudit::create(['order_id' => $order->id, 'user_id' => $request->user()->id, 'action' => 'cancelled', 'reason' => $reason]);
        return back()->with('success', 'Ordine annullato e posti liberati.');
    }

    public function receipt(Order $order)
    {
        $this->ensureBankOrder($order);
        abort_unless($order->transfer_receipt_path && Storage::disk('local')->exists($order->transfer_receipt_path), 404);
        return Storage::disk('local')->download($order->transfer_receipt_path);
    }

    private function ensureBankOrder(Order $order): void
    {
        abort_unless($order->payment_method === 'bank_transfer', 404);
    }
}
