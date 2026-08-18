<?php

namespace App\Services;

use App\Models\EventAddon;
use App\Models\EventCategory;

class OrderPricingService
{
    public function categoryPrice(EventCategory $category): float
    {
        $tier = $category->priceTiers()
            ->where(fn ($query) => $query->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($query) => $query->whereNull('ends_at')->orWhere('ends_at', '>', now()))
            ->orderByDesc('starts_at')
            ->orderByDesc('sort_order')
            ->first();

        return (float) ($tier?->price ?? $category->price);
    }

    public function addonAmount(array $ticket): float
    {
        if (! filter_var($ticket['shuttle_needed'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            return 0;
        }

        return (float) (EventAddon::where('code', 'shuttle')->where('is_active', true)->value('price') ?? 0);
    }
}
