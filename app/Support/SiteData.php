<?php

namespace App\Support;

/**
 * Content data ported verbatim from the Next.js reference's src/data/*.ts files
 * (activities.ts, menu.ts, rooms.ts). Static arrays only — no database, per the
 * frontend-migration scope for this stage.
 */
class SiteData
{
    public static function homeActivities(): array
    {
        return [
            [
                'id' => 'wellness',
                'name' => 'Wellness',
                'description' => 'Rejuvenate your body and mind through peaceful wellness experiences surrounded by the natural beauty of Amed.',
                'image' => '/images/activities/wellness.png',
            ],
            [
                'id' => 'fishing',
                'name' => 'Fishing',
                'description' => 'Set out into the waters of Amed for an authentic fishing experience, surrounded by the sea, mountains, and local coastal life.',
                'image' => '/images/activities/fishing.png',
            ],
            [
                'id' => 'connect',
                'name' => 'Connect',
                'description' => 'Discover Balinese heritage through the traditional art of writing on lontar leaves, a meaningful experience to share and remember.',
                'image' => '/images/activities/connect.png',
            ],
            [
                'id' => 'fishing-2',
                'name' => 'Fishing',
                'description' => 'Set out into the waters of Amed for an authentic fishing experience, surrounded by the sea, mountains, and local coastal life.',
                'image' => '/images/activities/fishing.png',
            ],
            [
                'id' => 'connect-2',
                'name' => 'Connect',
                'description' => 'Discover Balinese heritage through the traditional art of writing on lontar leaves, a meaningful experience to share and remember.',
                'image' => '/images/activities/connect.png',
            ],
        ];
    }

    public static function exceptionalExperiences(): array
    {
        return [
            [
                'id' => 'snorkeling-trip',
                'name' => 'Snorkeling Trip',
                'price' => 'Rp700.000/Pax',
                'includes' => ['Snorkeling equipment', 'Traditional boat', '3 spot snorkeling'],
                'image' => '/images/activities/snorkeling-trip.png',
            ],
            [
                'id' => 'fishing-bbq',
                'name' => 'Fishing trip & BBQ',
                'price' => 'Rp1.200.000/2 Pax',
                'includes' => [
                    'Boat and equipment fishing',
                    'Freshly caught fish grilled on-site',
                    'Complimentary side dishes and fish tools',
                    'Complimentary welcome drink',
                ],
                'image' => '/images/activities/fishing-bbq.png',
            ],
            [
                'id' => 'writing-lontar',
                'name' => 'Writing on Lontar',
                'price' => 'Rp200.000/Pax',
                'includes' => [
                    'Writing practice on lontar leaves',
                    'All materials propided',
                    'Afternoon tea after workshoop',
                    'Balinese sarong provided during activity',
                    'Complimentary photo session',
                ],
                'image' => '/images/activities/writing-lontar.png',
            ],
            [
                'id' => 'placeholder-experience-4',
                'name' => 'Yoga Session',
                'price' => 'Rp100.000/2 Pax',
                'includes' => [
                    'All materials provided Afternoon tea after worksho',
                    'All materials provided Afternoon tea after worksho',
                    'All materials provided Afternoon tea after worksho',
                    'All materials provided Afternoon tea after worksho',
                ],
                'image' => '/images/activities/activity-hero.png',
            ],
        ];
    }

    public static function signatureCocktails(): array
    {
        return [
            ['id' => 'cocktail-1', 'name' => 'Azure Sunset', 'description' => 'Vodka Peach Lemon Gin Blue Lime', 'image' => '/images/dining/barak-rooftop-and-bar/cocktail-1.png'],
            ['id' => 'cocktail-2', 'name' => 'Azure Sunset', 'description' => 'Vodka Peach Lemon Gin Blue Lime', 'image' => '/images/dining/barak-rooftop-and-bar/cocktail-2.png'],
            ['id' => 'cocktail-3', 'name' => 'Azure Sunset', 'description' => 'Vodka Peach Lemon Gin Blue Lime', 'image' => '/images/dining/barak-rooftop-and-bar/cocktail-3.png'],
            ['id' => 'cocktail-4', 'name' => 'Azure Sunset', 'description' => 'Vodka Peach Lemon Gin Blue Lime', 'image' => '/images/dining/barak-rooftop-and-bar/cocktail-4.png'],
        ];
    }

    public static function rooms(): array
    {
        return [
            [
                'id' => 'superior-double-twin', 'name' => 'Superior Double / Twin Room', 'ratePerNight' => 700000,
                'currency' => 'Rp', 'sizeSqm' => 24, 'maxGuests' => 5,
                'bedding' => '1 Large Double Bed OR 2 Single Beds', 'includesBreakfast' => true,
                'image' => '/images/rooms/room-card.png',
            ],
            [
                'id' => 'deluxe-room-pool-view', 'name' => 'Deluxe Room Pool View', 'ratePerNight' => 800000,
                'currency' => 'Rp', 'sizeSqm' => 30, 'maxGuests' => 5,
                'bedding' => '1 Large Double Bed OR 2 Single Beds', 'includesBreakfast' => true,
                'image' => '/images/rooms/room-card.png',
            ],
            [
                'id' => 'deluxe-double-sea-view', 'name' => 'Deluxe Double Room With Sea View', 'ratePerNight' => 950000,
                'currency' => 'Rp', 'sizeSqm' => 30, 'maxGuests' => 5,
                'bedding' => '1 Extra Large Double Bed', 'includesBreakfast' => true,
                'image' => '/images/rooms/room-card.png',
            ],
            [
                'id' => 'deluxe-twin-room', 'name' => 'Deluxe Twin Room', 'ratePerNight' => 950000,
                'currency' => 'Rp', 'sizeSqm' => 30, 'maxGuests' => 5,
                'bedding' => '2 Single Beds', 'includesBreakfast' => true,
                'image' => '/images/rooms/room-card.png',
            ],
            [
                'id' => 'triple-room-tv', 'name' => 'Triple Room With TV', 'ratePerNight' => 900000,
                'currency' => 'Rp', 'sizeSqm' => 33, 'maxGuests' => 5,
                'bedding' => '1 Large Double Bed + 1 Single Bed, OR 3 Single Beds', 'includesBreakfast' => true,
                'image' => '/images/rooms/room-card.png',
            ],
            [
                'id' => 'triple-room-pool-view', 'name' => 'Triple Room With Pool View', 'ratePerNight' => 900000,
                'currency' => 'Rp', 'sizeSqm' => 33, 'maxGuests' => 5,
                'bedding' => '1 Large Double Bed + 1 Single Bed', 'includesBreakfast' => true,
                'image' => '/images/rooms/room-card.png',
            ],
        ];
    }

    public static function familyRoom(): array
    {
        return [
            'id' => 'family-room', 'name' => 'Family Room', 'ratePerNight' => 700000,
            'currency' => 'Rp', 'sizeSqm' => 24, 'maxGuests' => 5,
            'bedding' => '1 Large Double Bed OR 2 Single Beds', 'includesBreakfast' => true,
            'image' => '/images/rooms/family-room-main.png',
        ];
    }
}
