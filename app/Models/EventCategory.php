<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventCategory extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'capacity' => 'integer', 'is_active' => 'boolean'];
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function priceTiers()
    {
        return $this->hasMany(EventCategoryPriceTier::class);
    }
}
