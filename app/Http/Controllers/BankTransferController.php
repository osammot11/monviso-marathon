<?php

namespace App\Http\Controllers;

use App\Jobs\SendBookingOtp;
use App\Models\BankTransferAudit;
use App\Models\Order;
use App\Services\BankTransferService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BankTransferController extends Controller
{
    public function index()
    {
        return view('booking.search');
    }

    public function show(string $token, BankTransferService $bank)
    {
        $order = $bank->resolveByPublicToken($token);
        return view('booking.show', [
            'order' => $order,
            'token' => $token,
            'bankSettings' => $bank->settings(),
            'qrDataUri' => $order->payment_method === 'bank_transfer' ? $bank->qrDataUri($order) : null,
        ]);
    }

    public function uploadReceipt(Request $request, string $token, BankTransferService $bank)
    {
        $order = $bank->resolveByPublicToken($token);
        abort_unless($order->payment_method === 'bank_transfer' && in_array($order->status, ['awaiting_transfer', 'transfer_review'], true), 409);
        $validated = $request->validate(['receipt' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120']);

        if ($order->transfer_receipt_path) {
            Storage::disk('local')->delete($order->transfer_receipt_path);
        }
        $path = $validated['receipt']->store("bank-receipts/{$order->id}", 'local');
        $order->update([
            'transfer_receipt_path' => $path,
            'transfer_receipt_uploaded_at' => now(),
            'status' => 'transfer_review',
            'transfer_review_reason' => 'Ricevuta caricata dal referente.',
        ]);
        BankTransferAudit::create(['order_id' => $order->id, 'action' => 'receipt_uploaded']);

        return back()->with('success', 'Ricevuta caricata. Il pagamento sarà verificato senza attivare automaticamente i biglietti.');
    }

    public function lookup(Request $request)
    {
        $validated = $request->validate(['booking_code' => 'required|string|max:20', 'email' => 'required|email']);
        $order = Order::where('booking_code', strtoupper($validated['booking_code']))
            ->where('payment_contact_email', strtolower($validated['email']))
            ->first();
        if (! $order) {
            return back()->withErrors(['booking_code' => 'Prenotazione non trovata. Controlla codice ed email.'])->withInput();
        }

        return $this->rotateTokenAndRedirect($order);
    }

    public function signedAccess(Order $order)
    {
        return $this->rotateTokenAndRedirect($order);
    }

    public function recovery()
    {
        return view('booking.recovery');
    }

    public function sendOtp(Request $request)
    {
        $email = strtolower($request->validate(['email' => 'required|email'])['email']);
        $order = Order::where('payment_contact_email', $email)->latest()->first();
        if ($order) {
            $otp = (string) random_int(100000, 999999);
            $order->update(['booking_otp_hash' => hash('sha256', $otp), 'booking_otp_expires_at' => now()->addMinutes(10)]);
            SendBookingOtp::dispatch($email, $order->payment_contact_name, $otp);
        }

        return back()->with('otp_sent', true)->with('recovery_email', $email);
    }

    public function verifyOtp(Request $request)
    {
        $validated = $request->validate(['email' => 'required|email', 'otp' => 'required|digits:6']);
        $order = Order::where('payment_contact_email', strtolower($validated['email']))
            ->where('booking_otp_hash', hash('sha256', $validated['otp']))
            ->where('booking_otp_expires_at', '>', now())
            ->latest()
            ->first();
        if (! $order) {
            return back()->withErrors(['otp' => 'Codice non valido o scaduto.'])->withInput();
        }
        $order->update(['booking_otp_hash' => null, 'booking_otp_expires_at' => null]);
        return $this->rotateTokenAndRedirect($order);
    }

    private function rotateTokenAndRedirect(Order $order)
    {
        $token = Str::random(64);
        $order->update(['public_token_hash' => hash('sha256', $token)]);
        return redirect()->route('booking.show', ['token' => $token]);
    }
}
