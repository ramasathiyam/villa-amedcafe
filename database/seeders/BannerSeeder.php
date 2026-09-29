<?php

namespace Database\Seeders;

use App\Models\Banner;
use Illuminate\Database\Seeder;

/**
 * Migrates the single sitewide banner (formerly PromoBannerSeeder, "Amed Escape") into one
 * row per page. Text and link are copied verbatim from what was in the database (the
 * original seeded content — never edited via Admin since). Each of the 6 original pages
 * gets its own image, which already existed on disk per page
 * (/images/{page}/promo-amed-escape.png, confirmed present for all but Activity).
 * Activity had no banner before this task, so it copies Home entirely — text, link, and
 * image — rather than inventing new copy. Idempotent (updateOrCreate, keyed on `page`),
 * same convention as every other content seeder in this project.
 */
class BannerSeeder extends Seeder
{
    public function run(): void
    {
        $eyebrow = 'Explore';
        $heading = 'Amed Escape';
        $body = 'Experience the peaceful charm of Amed, where tropical surroundings, ocean views, and authentic Balinese hospitality come together to create a memorable stay.';
        $linkUrl = null;

        $pages = [
            'home' => '/images/home/promo-amed-escape.png',
            'rooms' => '/images/rooms/promo-amed-escape.png',
            // No banner existed on Activity before — copies Home's image too, not just its text.
            'activities' => '/images/home/promo-amed-escape.png',
            'spa' => '/images/spa/promo-amed-escape.png',
            'contact' => '/images/contact/promo-amed-escape.png',
            'resto-amed-cafe' => '/images/dining/resto-amed-cafe/promo-amed-escape.png',
            'barak-rooftop-and-bar' => '/images/dining/barak-rooftop-and-bar/promo-amed-escape.png',
        ];

        foreach ($pages as $page => $image) {
            Banner::updateOrCreate(
                ['page' => $page],
                [
                    'eyebrow' => $eyebrow,
                    'heading' => $heading,
                    'body' => $body,
                    'background_image' => $image,
                    'link_url' => $linkUrl,
                    'is_active' => true,
                ]
            );
        }
    }
}
