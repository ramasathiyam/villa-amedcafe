<?php

namespace App\Models;

use App\Models\Concerns\HasAutoSlug;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'slug', 'name', 'rate_per_night', 'currency', 'size_sqm', 'max_guests', 'bedding',
    'includes_breakfast', 'image', 'is_featured', 'hotel_information', 'is_active', 'sort_order',
    'total_units',
])]
class Room extends Model
{
    use HasAutoSlug;

    protected function casts(): array
    {
        return [
            'rate_per_night' => 'integer',
            'size_sqm' => 'integer',
            'max_guests' => 'integer',
            'includes_breakfast' => 'boolean',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'total_units' => 'integer',
        ];
    }

    public function images(): HasMany
    {
        return $this->hasMany(RoomImage::class)->orderBy('sort_order');
    }

    public function bookingItems(): HasMany
    {
        return $this->hasMany(BookingItem::class);
    }

    public function ratePlans(): HasMany
    {
        return $this->hasMany(RatePlan::class)->orderBy('sort_order');
    }

    /**
     * Maps this model onto the array shape resources/views/components/cards/room-card.blade.php
     * already expects (ratePerNight, maxGuests, sizeSqm, includesBreakfast — the same
     * camelCase keys App\Support\SiteData::rooms() used to produce), so the public /room
     * page's Blade component needs no changes to consume Eloquent data.
     */
    /**
     * Lowest price among this room's active rate plans, falling back to
     * rooms.rate_per_night when it has none — reads whatever `ratePlans` already holds
     * in memory (never queries), so callers must eager-load it first to stay N+1-safe
     * across a list of rooms.
     */
    public function startingPricePerNight(): int
    {
        return $this->ratePlans->min('price_per_night') ?? $this->rate_per_night;
    }

    public function toRoomCardArray(): array
    {
        return [
            'id' => $this->slug,
            'name' => $this->name,
            'ratePerNight' => $this->startingPricePerNight(),
            'currency' => $this->currency,
            'sizeSqm' => $this->size_sqm,
            'maxGuests' => $this->max_guests,
            'bedding' => $this->bedding,
            'includesBreakfast' => $this->includes_breakfast,
            'image' => $this->image,
        ];
    }
}
