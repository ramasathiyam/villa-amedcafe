<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'slug', 'name', 'hero_title', 'hero_subtitle', 'hero_image',
    'intro_heading', 'intro_body', 'is_active', 'sort_order',
])]
class DiningVenue extends Model
{
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Admin routes bind by slug (/admin/dining/resto-amed-cafe), not numeric id — slugs
     * are already the fixed, unique, human-meaningful identifier for a venue.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function images(): HasMany
    {
        return $this->hasMany(DiningVenueImage::class)->orderBy('sort_order');
    }

    public function items(): HasMany
    {
        return $this->hasMany(DiningItem::class)->orderBy('sort_order');
    }

    public function testimonials(): HasMany
    {
        return $this->hasMany(Testimonial::class)->orderBy('sort_order');
    }

    /**
     * Filters the already-eager-loaded `images` relation by its `group` column (e.g.
     * 'gallery_1', 'feature', 'menu_highlight') — pure in-memory filtering, no extra
     * query, as long as `images` was eager-loaded by the caller.
     */
    public function imagesByGroup(string $group)
    {
        return $this->images->where('group', $group)->values();
    }
}
