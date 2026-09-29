<?php

namespace Database\Seeders;

use App\Models\Event;
use Illuminate\Database\Seeder;

/**
 * Migrates the "Halloween" content previously hardcoded on Home only. display_from/
 * display_until are deliberately left NULL — the original hardcoded block had no date
 * limits, and guessing a display window isn't this seeder's call; the admin should set
 * real dates via Admin > Events.
 */
class EventSeeder extends Seeder
{
    public function run(): void
    {
        Event::updateOrCreate(
            ['title' => 'Halloween'],
            [
                'label' => 'Seasonal Event',
                'description' => 'Experience an enchanting Halloween surrounded by the mystical beauty of Bali, where ancient traditions and captivating moments come together.',
                'background_image' => '/images/home/promo-halloween.png',
                'link_url' => null,
                'display_from' => null,
                'display_until' => null,
                'is_active' => true,
                'sort_order' => 0,
            ]
        );
    }
}
