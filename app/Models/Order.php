<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $guarded = []; // Permette il mass assignment su tutti i campi

    protected function casts(): array
    {
        return [
            'is_sports_association' => 'boolean',
            'total_amount' => 'decimal:2',
            'payment_due_at' => 'datetime',
            'booking_otp_expires_at' => 'datetime',
            'transfer_receipt_uploaded_at' => 'datetime',
            'transfer_reminder_sent_at' => 'datetime',
            'paid_at' => 'datetime',
            'transfer_verified_at' => 'datetime',
        ];
    }

    // Un ordine ha molti biglietti
    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function bankTransactions()
    {
        return $this->hasMany(BankTransaction::class, 'matched_order_id');
    }

    public function transferAudits()
    {
        return $this->hasMany(BankTransferAudit::class);
    }

    public function getIsTransferExpiredAttribute(): bool
    {
        return $this->payment_method === 'bank_transfer'
            && $this->payment_due_at?->isPast()
            && ! in_array($this->status, ['paid', 'cancelled'], true);
    }
}
