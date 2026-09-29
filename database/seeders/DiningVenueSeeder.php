<?php

namespace Database\Seeders;

use App\Models\DiningItem;
use App\Models\DiningVenue;
use App\Models\DiningVenueImage;
use Illuminate\Database\Seeder;

/**
 * Transcribed verbatim from resources/views/dining/resto-amed-cafe.blade.php and
 * dining/barak-rooftop-and-bar.blade.php. Slugs match the fixed public routes and must
 * not change. `group` values mirror each page's distinct photo strips.
 *
 * Note: the shared "Amed Café & Hotel Kebun Wayan" FeatureBlock heading/body text on the
 * Resto page is identical to copy reused on Home/Spa/other pages — it isn't captured as a
 * per-venue field here (out of the approved schema for this phase), only the accompanying
 * images are. Resto currently has no dining_items (no menu-item-shaped content exists for
 * it yet) — intentionally left empty rather than invented.
 */
class DiningVenueSeeder extends Seeder
{
    public function run(): void
    {
        $resto = DiningVenue::updateOrCreate(
            ['slug' => 'resto-amed-cafe'],
            [
                'name' => 'Resto Amed Cafe',
                'hero_title' => 'Resto Amed Cafe',
                'hero_subtitle' => 'The Balinese Style Hotel in Amed Bali',
                'hero_image' => '/images/dining/resto-amed-cafe/hero.png',
                'intro_heading' => 'Amed Cafe',
                'intro_body' => 'Nestled in the tranquil village of Amed, Kebun Wayan is a charming café and hotel surrounded by tropical gardens, offering a peaceful escape with the authentic beauty of Bali.',
                'sort_order' => 1,
            ]
        );

        $restoImages = [
            ['image' => '/images/dining/resto-amed-cafe/gallery-1-wings.png', 'alt_text' => 'Grilled chicken wings', 'group' => 'gallery_1', 'sort_order' => 1],
            ['image' => '/images/dining/resto-amed-cafe/gallery-2-tuna.png', 'alt_text' => 'Seared tuna with tropical salsa', 'group' => 'gallery_1', 'sort_order' => 2],
            ['image' => '/images/dining/resto-amed-cafe/gallery-3-croquette-beer.png', 'alt_text' => 'Croquettes served with a cold beer', 'group' => 'gallery_1', 'sort_order' => 3],
            ['image' => '/images/dining/resto-amed-cafe/feature-1-friends.png', 'alt_text' => 'Friends sharing a meal and drinks at Resto Amed Cafe', 'group' => 'feature', 'sort_order' => 1],
            ['image' => '/images/dining/resto-amed-cafe/feature-2-family.png', 'alt_text' => 'Family celebrating a meal together by the beach', 'group' => 'feature', 'sort_order' => 2],
            ['image' => '/images/dining/resto-amed-cafe/gallery-4-welcome.png', 'alt_text' => 'Welcome dessert plate', 'group' => 'gallery_2', 'sort_order' => 1],
            ['image' => '/images/dining/resto-amed-cafe/gallery-5-croquette.png', 'alt_text' => 'Croquettes plated with herbs', 'group' => 'gallery_2', 'sort_order' => 2],
            ['image' => '/images/dining/resto-amed-cafe/gallery-6-croquette-close.png', 'alt_text' => 'Close-up of croquettes with sauce', 'group' => 'gallery_2', 'sort_order' => 3],
        ];

        foreach ($restoImages as $image) {
            DiningVenueImage::updateOrCreate(
                ['dining_venue_id' => $resto->id, 'image' => $image['image']],
                $image + ['dining_venue_id' => $resto->id]
            );
        }

        $barak = DiningVenue::updateOrCreate(
            ['slug' => 'barak-rooftop-and-bar'],
            [
                'name' => 'Barak Rooftop and Bar',
                'hero_title' => 'Barak Rooftop and Bar',
                'hero_subtitle' => 'The Balinese Style Hotel in Amed Bali',
                'hero_image' => '/images/dining/barak-rooftop-and-bar/hero.png',
                'intro_heading' => 'Barak Rooftop and Bar',
                'intro_body' => 'Nestled in the tranquil village of Amed, Kebun Wayan is a charming café and hotel surrounded by tropical gardens, offering a peaceful escape with the authentic beauty of Bali.',
                'sort_order' => 2,
            ]
        );

        $barakImages = [
            ['image' => '/images/dining/barak-rooftop-and-bar/menu-1.png', 'alt_text' => 'Frosted berry cocktail with citrus', 'group' => 'menu_highlight', 'title' => 'Grileweaodk', 'description' => 'Lemon Butter, Garlic, Herbs', 'sort_order' => 1],
            ['image' => '/images/dining/barak-rooftop-and-bar/menu-2.png', 'alt_text' => 'Cocktail in a copper mug with mint and orange', 'group' => 'menu_highlight', 'title' => 'Grileweaodk', 'description' => 'Lemon Butter, Garlic, Herbs', 'sort_order' => 2],
            ['image' => '/images/dining/barak-rooftop-and-bar/menu-3.png', 'alt_text' => 'Blue curaçao cocktail with blackberry garnish', 'group' => 'menu_highlight', 'title' => 'Grileweaodk', 'description' => 'Lemon Butter, Garlic, Herbs', 'sort_order' => 3],
            ['image' => '/images/dining/barak-rooftop-and-bar/menu-4.png', 'alt_text' => 'Orange cocktail with citrus peel garnish', 'group' => 'menu_highlight', 'title' => 'Grileweaodk', 'description' => 'Lemon Butter, Garlic, Herbs', 'sort_order' => 4],
            ['image' => '/images/dining/barak-rooftop-and-bar/menu-5.png', 'alt_text' => 'Pink cocktail beside a bottle of gin', 'group' => 'menu_highlight', 'title' => 'Grileweaodk', 'description' => 'Lemon Butter, Garlic, Herbs', 'sort_order' => 5],
            ['image' => '/images/dining/barak-rooftop-and-bar/feature-friends.png', 'alt_text' => 'Guests sharing drinks and laughter at Barak Rooftop and Bar', 'group' => 'feature', 'sort_order' => 1],
            ['image' => '/images/dining/barak-rooftop-and-bar/gallery-1-welcome.png', 'alt_text' => 'Welcome dessert plate', 'group' => 'gallery_1', 'sort_order' => 1],
            ['image' => '/images/dining/barak-rooftop-and-bar/gallery-2-croquette.png', 'alt_text' => 'Croquettes plated with herbs', 'group' => 'gallery_1', 'sort_order' => 2],
            ['image' => '/images/dining/barak-rooftop-and-bar/gallery-3-croquette-close.png', 'alt_text' => 'Close-up of croquettes with sauce', 'group' => 'gallery_1', 'sort_order' => 3],
        ];

        foreach ($barakImages as $image) {
            DiningVenueImage::updateOrCreate(
                ['dining_venue_id' => $barak->id, 'image' => $image['image']],
                $image + ['dining_venue_id' => $barak->id]
            );
        }

        $cocktails = [
            ['name' => 'Azure Sunset', 'description' => 'Vodka Peach Lemon Gin Blue Lime', 'image' => '/images/dining/barak-rooftop-and-bar/cocktail-1.png', 'sort_order' => 1],
            ['name' => 'Azure Sunset', 'description' => 'Vodka Peach Lemon Gin Blue Lime', 'image' => '/images/dining/barak-rooftop-and-bar/cocktail-2.png', 'sort_order' => 2],
            ['name' => 'Azure Sunset', 'description' => 'Vodka Peach Lemon Gin Blue Lime', 'image' => '/images/dining/barak-rooftop-and-bar/cocktail-3.png', 'sort_order' => 3],
            ['name' => 'Azure Sunset', 'description' => 'Vodka Peach Lemon Gin Blue Lime', 'image' => '/images/dining/barak-rooftop-and-bar/cocktail-4.png', 'sort_order' => 4],
        ];

        foreach ($cocktails as $item) {
            DiningItem::updateOrCreate(
                ['dining_venue_id' => $barak->id, 'image' => $item['image']],
                $item + ['dining_venue_id' => $barak->id]
            );
        }
    }
}
