<?php

namespace Database\Seeders;

use App\Models\Activity;
use Illuminate\Database\Seeder;

/**
 * Transcribed verbatim from App\Support\SiteData::homeActivities() (show_on_home) and
 * exceptionalExperiences() (show_on_activity_page) — two previously-separate PHP arrays,
 * now one table distinguished by the two boolean flags. Duplicate/typo content
 * ("fishing-2", "connect-2", "propided", "workshoop") is preserved as-is, matching what's
 * currently live — not corrected here.
 */
class ActivitySeeder extends Seeder
{
    public function run(): void
    {
        $homeActivities = [
            [
                'slug' => 'wellness', 'name' => 'Wellness',
                'description' => 'Rejuvenate your body and mind through peaceful wellness experiences surrounded by the natural beauty of Amed.',
                'image' => '/images/activities/wellness.png', 'sort_order' => 1,
            ],
            [
                'slug' => 'fishing', 'name' => 'Fishing',
                'description' => 'Set out into the waters of Amed for an authentic fishing experience, surrounded by the sea, mountains, and local coastal life.',
                'image' => '/images/activities/fishing.png', 'sort_order' => 2,
            ],
            [
                'slug' => 'connect', 'name' => 'Connect',
                'description' => 'Discover Balinese heritage through the traditional art of writing on lontar leaves, a meaningful experience to share and remember.',
                'image' => '/images/activities/connect.png', 'sort_order' => 3,
            ],
            [
                'slug' => 'fishing-2', 'name' => 'Fishing',
                'description' => 'Set out into the waters of Amed for an authentic fishing experience, surrounded by the sea, mountains, and local coastal life.',
                'image' => '/images/activities/fishing.png', 'sort_order' => 4,
            ],
            [
                'slug' => 'connect-2', 'name' => 'Connect',
                'description' => 'Discover Balinese heritage through the traditional art of writing on lontar leaves, a meaningful experience to share and remember.',
                'image' => '/images/activities/connect.png', 'sort_order' => 5,
            ],
        ];

        foreach ($homeActivities as $activity) {
            Activity::updateOrCreate(
                ['slug' => $activity['slug']],
                $activity + ['show_on_home' => true, 'show_on_activity_page' => false]
            );
        }

        $exceptionalExperiences = [
            [
                'slug' => 'snorkeling-trip', 'name' => 'Snorkeling Trip', 'price_label' => 'Rp700.000/Pax',
                'includes' => ['Snorkeling equipment', 'Traditional boat', '3 spot snorkeling'],
                'image' => '/images/activities/snorkeling-trip.png', 'sort_order' => 1,
            ],
            [
                'slug' => 'fishing-bbq', 'name' => 'Fishing trip & BBQ', 'price_label' => 'Rp1.200.000/2 Pax',
                'includes' => [
                    'Boat and equipment fishing',
                    'Freshly caught fish grilled on-site',
                    'Complimentary side dishes and fish tools',
                    'Complimentary welcome drink',
                ],
                'image' => '/images/activities/fishing-bbq.png', 'sort_order' => 2,
            ],
            [
                'slug' => 'writing-lontar', 'name' => 'Writing on Lontar', 'price_label' => 'Rp200.000/Pax',
                'includes' => [
                    'Writing practice on lontar leaves',
                    'All materials propided',
                    'Afternoon tea after workshoop',
                    'Balinese sarong provided during activity',
                    'Complimentary photo session',
                ],
                'image' => '/images/activities/writing-lontar.png', 'sort_order' => 3,
            ],
            [
                'slug' => 'placeholder-experience-4', 'name' => 'Yoga Session', 'price_label' => 'Rp100.000/2 Pax',
                'includes' => [
                    'All materials provided Afternoon tea after worksho',
                    'All materials provided Afternoon tea after worksho',
                    'All materials provided Afternoon tea after worksho',
                    'All materials provided Afternoon tea after worksho',
                ],
                'image' => '/images/activities/activity-hero.png', 'sort_order' => 4,
            ],
        ];

        foreach ($exceptionalExperiences as $activity) {
            Activity::updateOrCreate(
                ['slug' => $activity['slug']],
                $activity + ['show_on_home' => false, 'show_on_activity_page' => true]
            );
        }
    }
}
