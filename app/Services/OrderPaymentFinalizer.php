<?php

namespace App\Services;

use App\Jobs\SendTicketEmail;
use App\Models\Order;
use Illuminate\Support\Str;

class OrderPaymentFinalizer
{
    public function finalize(Order $order): void
    {
        $order->loadMissing('tickets');

        if ($order->status !== 'paid') {
            $order->update(['status' => 'paid', 'paid_at' => $order->paid_at ?? now()]);
        }

        foreach ($order->tickets as $ticket) {
            $wasActive = $ticket->status === 'active';

            $ticket->update([
                'status' => 'active',
                'unique_ticket_code' => $ticket->unique_ticket_code ?? 'TKT_' . strtoupper(Str::random(8)),
            ]);

            if (! $wasActive) {
                SendTicketEmail::dispatch($ticket);
            }
        }
    }
}
