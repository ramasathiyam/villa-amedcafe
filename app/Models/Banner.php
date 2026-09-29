<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * One row per page (see BannerSeeder for the fixed 7-page list), edited via Admin's
 * Banner index/edit — no create/delete, the page list is fixed. Was "PromoBanner" (a
 * single sitewide row) before this task; renamed because "Promo Banner" described one
 * piece of content, not what the feature actually is now.
 */
#[Fillable(['page', 'eyebrow', 'heading', 'body', 'background_image', 'link_url', 'is_active'])]
class Banner extends Model
{
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Admin routes bind by page key (/admin/banners/{banner}/edit resolves by `page`, not
     * id) — same pattern as Page::getRouteKeyName() for Site Pages.
     */
    public function getRouteKeyName(): string
    {
        return 'page';
    }
}
