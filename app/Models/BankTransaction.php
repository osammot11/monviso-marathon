<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankTransaction extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['booked_at' => 'date', 'amount' => 'decimal:2', 'raw_data' => 'array'];
    }

    public function matchedOrder()
    {
        return $this->belongsTo(Order::class, 'matched_order_id');
    }
}
