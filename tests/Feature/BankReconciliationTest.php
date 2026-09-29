<?php

namespace Tests\Feature;

use App\Models\BankImportProfile;
use App\Models\BankTransaction;
use App\Models\EventCategory;
use App\Models\Order;
use App\Services\BankReconciliationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class BankReconciliationTest extends TestCase
{
    use RefreshDatabase;

    public function test_exact_eur_transfer_finalizes_idempotently_and_duplicate_is_ignored(): void
    {
        Queue::fake();
        $order = $this->makeOrder();
        $profile = $this->profile();
        $path = tempnam(sys_get_temp_dir(), 'bank-test-');
        file_put_contents($path, "Data;Importo;Valuta;Causale;ID\n18/08/2026;30,00;EUR;Quota {$order->bank_reference};MOV-1\n");

        $service = app(BankReconciliationService::class);
        $first = $service->import($path, $profile);
        $second = $service->import($path, $profile);
        @unlink($path);

        $this->assertSame(1, $first['matched']);
        $this->assertSame(1, $second['duplicates']);
        $this->assertSame('paid', $order->refresh()->status);
        $this->assertSame('active', $order->tickets()->first()->status);
        $this->assertSame(1, BankTransaction::count());
    }

    public function test_wrong_amount_goes_to_review_without_activating_tickets(): void
    {
        Queue::fake();
        $order = $this->makeOrder();
        $path = tempnam(sys_get_temp_dir(), 'bank-test-');
        file_put_contents($path, "Data;Importo;Valuta;Causale;ID\n18/08/2026;20,00;EUR;Quota {$order->bank_reference};MOV-2\n");
        $stats = app(BankReconciliationService::class)->import($path, $this->profile());
        @unlink($path);

        $this->assertSame(1, $stats['review']);
        $this->assertSame('transfer_review', $order->refresh()->status);
        $this->assertSame('pending', $order->tickets()->first()->status);
    }

    public function test_transfer_booked_in_time_reactivates_order_even_if_imported_after_expiry(): void
    {
        Queue::fake();
        $order = $this->makeOrder();
        $order->update(['status' => 'expired']);
        $transaction = BankTransaction::create([
            'fingerprint' => hash('sha256', 'late'), 'external_id' => 'MOV-LATE', 'booked_at' => '2026-08-18',
            'amount' => 30, 'currency' => 'EUR', 'description' => $order->bank_reference,
        ]);
        $this->assertSame('matched', app(BankReconciliationService::class)->match($transaction));
        $this->assertSame('paid', $order->refresh()->status);
    }

    private function profile(): BankImportProfile
    {
        return BankImportProfile::create(['name' => 'Test', 'delimiter' => ';', 'date_format' => 'd/m/Y', 'mapping' => [
            'date' => 'Data', 'amount' => 'Importo', 'currency' => 'Valuta', 'description' => 'Causale', 'external_id' => 'ID',
        ]]);
    }

    private function makeOrder(): Order
    {
        $category = EventCategory::where('name', 'M20K')->firstOrFail();
        $order = Order::create([
            'group_code' => 'MM26_RECON_'.uniqid(), 'booking_code' => 'MON26-'.strtoupper(substr(uniqid(), -5)),
            'total_amount' => 30, 'payment_method' => 'bank_transfer', 'status' => 'awaiting_transfer',
            'payment_contact_name' => 'Referente', 'payment_contact_email' => 'ref@example.it',
            'bank_reference' => 'MM26-'.strtoupper(substr(uniqid(), -5)),
            'created_at' => '2026-08-17 10:00:00', 'payment_due_at' => '2026-08-22 10:00:00',
        ]);
        $order->tickets()->create([
            'event_category_id' => $category->id, 'first_name' => 'Mara', 'last_name' => 'Bianchi',
            'route_choice' => $category->name, 'dob' => '1992-02-02', 'birth_place' => 'Pescara',
            'residence_address' => 'Via Mare 2', 'city' => 'Pescara', 'zip_code' => '65100',
            'province' => 'Pescara', 'region' => 'Abruzzo', 'country' => 'Italia', 'nationality' => 'IT',
            'email' => 'mara@example.it', 'phone' => '3330000000', 'tshirt_size' => 'S',
            'shuttle_needed' => false, 'celiac' => false, 'price_paid' => 30, 'payment_tag' => 'Bonifico', 'status' => 'pending',
        ]);
        return $order->refresh();
    }
}
