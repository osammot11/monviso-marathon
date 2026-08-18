<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankTransferAudit extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
