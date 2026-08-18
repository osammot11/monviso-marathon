<?php

namespace App\Services;

use App\Models\EventCategory;
use Illuminate\Validation\ValidationException;

class CategoryCapacityService
{
    public function lockAndResolve(array $tickets): array
    {
        $requested = collect($tickets)->countBy('route_choice');
        $categories = EventCategory::whereIn('name', $requested->keys())
            ->where('is_active', true)
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('name');

        if ($categories->count() !== $requested->count()) {
            throw ValidationException::withMessages(['tickets' => 'Una delle categorie selezionate non è disponibile.']);
        }

        foreach ($requested as $name => $quantity) {
            $category = $categories[$name];
            if ($category->capacity === null) {
                continue;
            }

            $reserved = $category->tickets()
                ->whereHas('order', fn ($query) => $query->whereIn('status', ['paid', 'awaiting_transfer', 'transfer_review']))
                ->count();

            if (($reserved + $quantity) > $category->capacity) {
                throw ValidationException::withMessages([
                    'tickets' => "Posti insufficienti per {$category->name}: ne restano ".max(0, $category->capacity - $reserved).'.',
                ]);
            }
        }

        return $categories->all();
    }
}
