<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'dob' => 'date', 'price_paid' => 'decimal:2', 'base_price' => 'decimal:2',
            'addon_amount' => 'decimal:2', 'shuttle_needed' => 'boolean', 'celiac' => 'boolean',
        ];
    }

    // Un biglietto appartiene a un ordine
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function category()
    {
        return $this->belongsTo(EventCategory::class, 'event_category_id');
    }
}
