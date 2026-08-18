<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventCategoryPriceTier extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'starts_at' => 'datetime', 'ends_at' => 'datetime'];
    }

    public function category()
    {
        return $this->belongsTo(EventCategory::class, 'event_category_id');
    }
}
