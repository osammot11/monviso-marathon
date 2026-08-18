<?php

namespace App\Console\Commands;

use App\Jobs\SendBankTransferReminder;
use App\Models\BankTransferAudit;
use App\Models\Order;
use Illuminate\Console\Command;

class MaintainBankTransfers extends Command
{
    protected $signature = 'bank-transfers:maintain';
    protected $description = 'Invia promemoria e scade le prenotazioni con bonifico non saldato';

    public function handle(): int
    {
        Order::where('payment_method', 'bank_transfer')
            ->whereIn('status', ['awaiting_transfer', 'transfer_review'])
            ->whereNull('transfer_reminder_sent_at')
            ->where('created_at', '<=', now()->subDays(3))
            ->where('payment_due_at', '>', now())
            ->each(function (Order $order) {
                SendBankTransferReminder::dispatch($order->id);
                $order->update(['transfer_reminder_sent_at' => now()]);
                BankTransferAudit::create(['order_id' => $order->id, 'action' => 'reminder_queued']);
            });

        Order::where('payment_method', 'bank_transfer')
            ->whereIn('status', ['awaiting_transfer', 'transfer_review'])
            ->where('payment_due_at', '<=', now())
            ->each(function (Order $order) {
                $order->update(['status' => 'expired']);
                BankTransferAudit::create(['order_id' => $order->id, 'action' => 'expired']);
            });

        return self::SUCCESS;
    }
}
