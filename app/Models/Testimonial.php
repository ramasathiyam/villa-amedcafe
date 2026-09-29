<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['dining_venue_id', 'quote', 'name', 'rating', 'is_active', 'sort_order'])]
class Testimonial extends Model
{
    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function venue(): BelongsTo
    {
        return $this->belongsTo(DiningVenue::class, 'dining_venue_id');
    }

    /**
     * Global testimonials (dining_venue_id NULL) plus any assigned to the given venue.
     * A plain ->where('dining_venue_id', $venueId) — or DiningVenue's own HasMany, which
     * applies that same implicit constraint — would silently exclude the NULL/global rows.
     */
    public function scopeApplicableToVenue(Builder $query, int $venueId): Builder
    {
        return $query->where(function (Builder $q) use ($venueId) {
            $q->whereNull('dining_venue_id')
                ->orWhere('dining_venue_id', $venueId);
        });
    }
}
