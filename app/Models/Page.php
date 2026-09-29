<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['slug', 'hero_title', 'hero_subtitle', 'hero_image', 'intro_heading', 'intro_body'])]
class Page extends Model
{
    /**
     * Admin routes bind by slug (/admin/pages/home), not numeric id — same reasoning
     * as DiningVenue::getRouteKeyName(): slugs are the fixed, human-meaningful identifier
     * for these rows, and there are only ever 4 of them (home, rooms, activities, spa).
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
