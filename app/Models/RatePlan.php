<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'room_id', 'name', 'description', 'max_guests', 'price_per_night',
    'compare_at_price_per_night', 'includes_breakfast', 'is_active', 'sort_order',
])]
class RatePlan extends Model
{
    protected function casts(): array
    {
        return [
            'max_guests' => 'integer',
            'price_per_night' => 'integer',
            'compare_at_price_per_night' => 'integer',
            'includes_breakfast' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }
}
