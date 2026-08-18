<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\BrevoMailer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

class SendBankTransferReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $orderId) {}

    public function handle(BrevoMailer $mailer): void
    {
        $order = Order::findOrFail($this->orderId);
        if (! in_array($order->status, ['awaiting_transfer', 'transfer_review'], true)) {
            return;
        }
        $link = URL::temporarySignedRoute('booking.signed', now()->addDays(14), ['order' => $order->id]);
        $due = $order->payment_due_at->timezone('Europe/Rome')->format('d/m/Y H:i');
        $mailer->send(
            $order->payment_contact_email,
            $order->payment_contact_name,
            'Promemoria bonifico - '.$order->booking_code,
            "<h2>La prenotazione scade il {$due}</h2><p>Se hai già pagato non devi fare nulla: riconcilieremo automaticamente il movimento.</p><p><a href='{$link}'>Controlla la prenotazione</a></p>",
        );
    }
}
