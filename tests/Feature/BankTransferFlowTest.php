<?php

namespace Tests\Feature;

use App\Jobs\SendBankTransferInstructions;
use App\Jobs\SendBankTransferReminder;
use App\Models\EventCategory;
use App\Models\EventAddon;
use App\Models\EventCategoryPriceTier;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use App\Services\BankTransferService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BankTransferFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        foreach ([
            'bank_transfer_enabled' => '1', 'bank_account_holder' => 'ASD Pescara Runners',
            'bank_iban' => 'IT60X0542811101000000123456', 'bank_bic' => 'BPBAIT3BXXX',
            'event_date' => '2027-05-16', 'bank_transfer_cutoff_days' => '7',
            'bank_transfer_reservation_days' => '5',
        ] as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
    }

    public function test_checkout_creates_reserved_bank_order_with_reference_and_magic_access(): void
    {
        Queue::fake();
        $response = $this->post(route('checkout.store'), $this->checkoutPayload());

        $order = Order::with('tickets')->sole();
        $response->assertRedirect();
        $this->assertStringContainsString('/prenotazione/', $response->headers->get('Location'));
        $this->assertSame('awaiting_transfer', $order->status);
        $this->assertMatchesRegularExpression('/^MAR2027-[A-Z0-9]{5}$/', $order->booking_code);
        $this->assertMatchesRegularExpression('/^PHM27-[A-Z0-9]{5}$/', $order->bank_reference);
        $this->assertSame('referente@example.it', $order->payment_contact_email);
        $this->assertTrue($order->payment_due_at->isBetween(now()->addDays(4), now()->addDays(6)));
        $this->assertSame('30.00', $order->total_amount);
        $this->assertSame('pending', $order->tickets->first()->status);
        Queue::assertPushed(SendBankTransferInstructions::class, fn ($job) => $job->orderId === $order->id);
        $this->get($response->headers->get('Location'))
            ->assertOk()
            ->assertSee('Completa il bonifico')
            ->assertSee($order->bank_reference);
    }

    public function test_epc_qr_payload_contains_exact_payment_data(): void
    {
        $order = $this->makeBankOrder();
        $payload = app(BankTransferService::class)->epcPayload($order);

        $this->assertStringContainsString("ASD Pescara Runners\nIT60X0542811101000000123456\nEUR30.00", $payload);
        $this->assertStringContainsString("\n{$order->bank_reference}\n", $payload);
    }

    public function test_active_price_tier_and_addon_are_snapshotted_in_order_total(): void
    {
        Queue::fake();
        $category = EventCategory::where('name', '21KM Competitiva')->firstOrFail();
        EventCategoryPriceTier::where('event_category_id', $category->id)->delete();
        EventCategoryPriceTier::create([
            'event_category_id' => $category->id, 'name' => 'Seconda fascia', 'price' => 35,
            'starts_at' => now()->subDay(), 'ends_at' => now()->addDay(),
        ]);
        EventAddon::where('code', 'shuttle')->update(['price' => 4]);
        $payload = $this->checkoutPayload();
        $payload['tickets'][0]['shuttle_needed'] = '1';

        $this->post(route('checkout.store'), $payload)->assertRedirect();
        $order = Order::with('tickets')->sole();
        $this->assertSame('39.00', $order->total_amount);
        $this->assertSame('35.00', $order->tickets->first()->base_price);
        $this->assertSame('4.00', $order->tickets->first()->addon_amount);
    }

    public function test_optional_receipt_is_private_and_never_marks_order_paid(): void
    {
        Storage::fake('local');
        $order = $this->makeBankOrder();
        $token = 'A'.str_repeat('b', 63);
        $order->update(['public_token_hash' => hash('sha256', $token)]);

        $this->post(route('booking.receipt', $token), ['receipt' => UploadedFile::fake()->image('ricevuta.jpg')])
            ->assertRedirect();

        $order->refresh();
        $this->assertSame('transfer_review', $order->status);
        $this->assertNotNull($order->transfer_receipt_path);
        Storage::disk('local')->assertExists($order->transfer_receipt_path);
        $this->assertNull($order->paid_at);
        $this->assertSame('pending', $order->tickets()->first()->status);
    }

    public function test_capacity_counts_pending_reservations_and_prevents_overbooking(): void
    {
        EventCategory::where('name', '21KM Competitiva')->update(['capacity' => 1]);
        Queue::fake();
        $this->post(route('checkout.store'), $this->checkoutPayload())->assertRedirect();
        $this->from(route('iscrizione'))->post(route('checkout.store'), $this->checkoutPayload('secondo@example.it'))
            ->assertRedirect(route('iscrizione'))
            ->assertSessionHasErrors('tickets');
        $this->assertSame(1, Order::where('payment_method', 'bank_transfer')->count());
    }

    public function test_maintenance_sends_one_reminder_then_expires_and_releases_capacity(): void
    {
        Queue::fake();
        $order = $this->makeBankOrder();
        $order->forceFill(['created_at' => now()->subDays(3)->subMinute(), 'payment_due_at' => now()->addDay()])->save();
        Artisan::call('bank-transfers:maintain');
        Queue::assertPushed(SendBankTransferReminder::class, 1);
        $this->assertNotNull($order->refresh()->transfer_reminder_sent_at);

        Artisan::call('bank-transfers:maintain');
        Queue::assertPushed(SendBankTransferReminder::class, 1);
        $order->update(['payment_due_at' => now()->subMinute()]);
        Artisan::call('bank-transfers:maintain');
        $this->assertSame('expired', $order->refresh()->status);
    }

    public function test_otp_request_does_not_reveal_whether_email_exists(): void
    {
        Queue::fake();
        $known = $this->post(route('booking.otp.send'), ['email' => 'referente@example.it']);
        $unknown = $this->post(route('booking.otp.send'), ['email' => 'nessuno@example.it']);
        $known->assertSessionHas('otp_sent', true);
        $unknown->assertSessionHas('otp_sent', true);
        $this->assertSame($known->getStatusCode(), $unknown->getStatusCode());
    }

    public function test_authenticated_admin_can_open_order_level_reconciliation_dashboard(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.bank.index'))
            ->assertOk()
            ->assertSee('Bonifici intelligenti')
            ->assertSee('Importa estratto conto');
    }

    private function checkoutPayload(string $email = 'REFERENTE@EXAMPLE.IT'): array
    {
        return [
            'payment_method' => 'bank_transfer', 'payment_contact_name' => 'Mario Rossi',
            'payment_contact_email' => $email, 'is_sports_association' => '0', 'is_mutua_member' => '0',
            'tickets' => [[
                'first_name' => 'Mario', 'last_name' => 'Rossi', 'route_choice' => '21KM Competitiva',
                'dob' => '1990-01-02', 'birth_place' => 'Pescara', 'residence_address' => 'Via Roma 1',
                'city' => 'Pescara', 'zip_code' => '65100', 'province' => 'Pescara', 'region' => 'Abruzzo',
                'country' => 'Italia', 'nationality' => 'IT', 'codice_fiscale' => 'RSSMRA90A02G482X',
                'email' => 'mario@example.it', 'phone' => '3331234567', 'tshirt_size' => 'M',
                'shuttle_needed' => '0', 'celiac' => '0',
            ]],
        ];
    }

    private function makeBankOrder(): Order
    {
        $category = EventCategory::where('name', '21KM Competitiva')->firstOrFail();
        $order = Order::create([
            'group_code' => 'PHM_TEST_'.uniqid(), 'booking_code' => 'MAR2027-ABCDE',
            'total_amount' => 30, 'payment_method' => 'bank_transfer', 'status' => 'awaiting_transfer',
            'payment_contact_name' => 'Mario Rossi', 'payment_contact_email' => 'referente@example.it',
            'bank_reference' => 'PHM27-ABCDE', 'payment_due_at' => now()->addDays(5),
        ]);
        $order->tickets()->create(array_merge($this->checkoutPayload()['tickets'][0], [
            'event_category_id' => $category->id, 'price_paid' => 30, 'payment_tag' => 'Bonifico', 'status' => 'pending',
        ]));
        return $order->refresh();
    }
}
