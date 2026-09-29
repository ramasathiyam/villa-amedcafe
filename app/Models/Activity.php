<?php

namespace App\Models;

use App\Models\Concerns\HasAutoSlug;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'slug', 'name', 'description', 'price_label', 'includes', 'image',
    'show_on_home', 'show_on_activity_page', 'is_active', 'sort_order',
])]
class Activity extends Model
{
    use HasAutoSlug;

    protected function casts(): array
    {
        return [
            'includes' => 'array',
            'show_on_home' => 'boolean',
            'show_on_activity_page' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * Maps this model onto the array shape
     * resources/views/components/cards/activity-card.blade.php expects. Used exclusively
     * by the "Exceptional Experiences" grid on /activity (confirmed single call site:
     * PageController::activity()) — that grid shows Name/Image/Includes only, never
     * Description or Price, so both are deliberately omitted here. 'price'/'description'
     * used to be included (leftover from this method's original, more general shape) and
     * leaked through activity-card.blade.php's own `!empty($activity['price'])`/
     * `!empty($activity['description'])` checks, which is why they rendered there.
     */
    public function toActivityCardArray(): array
    {
        return [
            'id' => $this->slug,
            'name' => $this->name,
            'includes' => $this->includes,
            'image' => $this->image,
        ];
    }

    /**
     * Home page teaser cards use a narrower shape than toActivityCardArray() above —
     * name/description/image only. Deliberately omits 'price'/'includes' even when this
     * row also has show_on_activity_page=true with real price_label/includes data for
     * that other context: activity-card.blade.php shows a price badge/includes list
     * whenever those array keys are non-empty, and Home's teaser design (per the CMS
     * field hint: "Description — used by the Home page's teaser cards") never shows
     * price or includes, only the "Exceptional Experiences" grid does.
     */
    public function toHomeTeaserArray(): array
    {
        return [
            'id' => $this->slug,
            'name' => $this->name,
            'description' => $this->description,
            'image' => $this->image,
        ];
    }
}
