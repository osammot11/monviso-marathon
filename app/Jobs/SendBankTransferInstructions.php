<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\BankTransferService;
use App\Services\BrevoMailer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class SendBankTransferInstructions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $orderId) {}

    public function handle(BankTransferService $bank, BrevoMailer $mailer): void
    {
        $order = Order::findOrFail($this->orderId);
        $settings = $bank->settings();
        $link = URL::temporarySignedRoute('booking.signed', now()->addDays(30), ['order' => $order->id]);
        $amount = number_format((float) $order->total_amount, 2, ',', '.');
        $due = $order->payment_due_at->timezone('Europe/Rome')->format('d/m/Y H:i');
        $html = "<h2>Completa la prenotazione {$order->booking_code}</h2>
            <p>Importo: <strong>€ {$amount}</strong></p>
            <p>Beneficiario: <strong>{$settings['account_holder']}</strong><br>IBAN: <strong>{$settings['iban']}</strong><br>Causale: <strong>{$order->bank_reference}</strong></p>
            <p>Effettua il bonifico entro il <strong>{$due}</strong>. La ricevuta è facoltativa: il pagamento verrà riconciliato automaticamente.</p>
            <p><a href='{$link}'>Apri la tua prenotazione</a></p>";
        $mailer->send($order->payment_contact_email, $order->payment_contact_name, 'Istruzioni bonifico - '.$order->booking_code, $html);
    }
}
