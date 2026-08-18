<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventAddon extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'is_active' => 'boolean'];
    }
}
