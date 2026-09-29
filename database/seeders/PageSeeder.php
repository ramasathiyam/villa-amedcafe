<?php

namespace Database\Seeders;

use App\Models\DiningVenue;
use App\Models\Page;
use Illuminate\Database\Seeder;

/**
 * Seeds the public pages that get a Site Pages CMS entry, with content copied
 * verbatim (including embedded line breaks) from what was previously hardcoded
 * directly in each page's Blade view — this seeder is what makes that migration
 * content-neutral (the public pages render identically before/after).
 */
class PageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            [
                'slug' => 'home',
                'hero_title' => "Amed Café &\nHotel Kebun Wayan",
                'hero_subtitle' => 'The Balinese Style Hotel in Amed Bali',
                'hero_image' => '/images/hero/home-hero.png',
                'intro_heading' => 'About Us',
                'intro_body' => 'Nestled in the tranquil village of Amed, Kebun Wayan is a charming café and hotel surrounded by tropical gardens, offering a peaceful escape with the authentic beauty of Bali.',
            ],
            [
                'slug' => 'rooms',
                'hero_title' => 'Rooms',
                'hero_subtitle' => 'The Balinese Style Hotel in Amed Bali',
                'hero_image' => '/images/rooms/hero.png',
                'intro_heading' => 'Rooms',
                'intro_body' => 'Nestled in the tranquil village of Amed, Kebun Wayan is a charming café and hotel surrounded by tropical gardens, offering a peaceful escape with the authentic beauty of Bali.',
            ],
            [
                'slug' => 'activities',
                'hero_title' => "Discover More\nExperience More",
                'hero_subtitle' => 'From the sea to the shore, discover unique experiences and activities that bring you closer to the beauty of Amed.',
                'hero_image' => '/images/activities/activity-hero.png',
                'intro_heading' => 'Activities',
                'intro_body' => "From untamed wilderness to the heart of Ubud, The Kayon Hotels & Resorts is a family-owned collection and each uniquely designed\nyet united by the same soul.",
            ],
            [
                'slug' => 'spa',
                'hero_title' => 'Spa',
                'hero_subtitle' => 'The Balinese Style Hotel in Amed Bali',
                'hero_image' => '/images/spa/spa-hero.png',
                'intro_heading' => 'Spa',
                'intro_body' => 'Nestled in the tranquil village of Amed, Kebun Wayan is a charming café and hotel surrounded by tropical gardens, offering a peaceful escape with the authentic beauty of Bali.',
            ],
        ];

        foreach ($pages as $page) {
            Page::updateOrCreate(['slug' => $page['slug']], $page);
        }

        // Resto Amed Café / Barak Rooftop & Bar: hero/intro content used to live on
        // dining_venues and is being moved to Site Pages so there's one editor for it,
        // not two. firstOrCreate (not updateOrCreate) — copy from dining_venues only the
        // FIRST time this runs; once the Page row exists, re-seeding must never overwrite
        // whatever an admin has since edited through Site Pages with the (now possibly
        // stale) dining_venues value.
        foreach (['resto-amed-cafe', 'barak-rooftop-and-bar'] as $slug) {
            $venue = DiningVenue::where('slug', $slug)->first();

            if (! $venue) {
                continue;
            }

            Page::firstOrCreate(['slug' => $slug], [
                'hero_title' => $venue->hero_title,
                'hero_subtitle' => $venue->hero_subtitle,
                'hero_image' => $venue->hero_image,
                'intro_heading' => $venue->intro_heading,
                'intro_body' => $venue->intro_body,
            ]);
        }
    }
}
