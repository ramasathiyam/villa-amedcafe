<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['dining_venue_id', 'name', 'description', 'image', 'is_active', 'sort_order'])]
class DiningItem extends Model
{
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function venue(): BelongsTo
    {
        return $this->belongsTo(DiningVenue::class, 'dining_venue_id');
    }

    /**
     * Maps this model onto the array shape
     * resources/views/components/cards/menu-item-card.blade.php already expects
     * (App\Support\SiteData::signatureCocktails()'s old shape), so that component needs
     * no changes to consume Eloquent data.
     */
    public function toMenuItemArray(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'image' => $this->image,
        ];
    }
}
