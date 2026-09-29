<?php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\RoomImage;
use Illuminate\Database\Seeder;

/**
 * Transcribed verbatim from App\Support\SiteData::rooms()/familyRoom() and the
 * "Hotel Information" paragraph previously hardcoded in resources/views/room/index.blade.php.
 *
 * The Family Room's two detail photos both point at the same file
 * (images/rooms/family-room-detail.png) in the current frontend — that duplication is
 * preserved here rather than invented away, since it reflects what's actually live.
 */
class RoomSeeder extends Seeder
{
    public function run(): void
    {
        $rooms = [
            [
                'slug' => 'superior-double-twin', 'name' => 'Superior Double / Twin Room', 'rate_per_night' => 700000,
                'currency' => 'Rp', 'size_sqm' => 24, 'max_guests' => 5,
                'bedding' => '1 Large Double Bed OR 2 Single Beds', 'includes_breakfast' => true,
                'image' => '/images/rooms/room-card.png', 'sort_order' => 1,
            ],
            [
                'slug' => 'deluxe-room-pool-view', 'name' => 'Deluxe Room Pool View', 'rate_per_night' => 800000,
                'currency' => 'Rp', 'size_sqm' => 30, 'max_guests' => 5,
                'bedding' => '1 Large Double Bed OR 2 Single Beds', 'includes_breakfast' => true,
                'image' => '/images/rooms/room-card.png', 'sort_order' => 2,
            ],
            [
                'slug' => 'deluxe-double-sea-view', 'name' => 'Deluxe Double Room With Sea View', 'rate_per_night' => 950000,
                'currency' => 'Rp', 'size_sqm' => 30, 'max_guests' => 5,
                'bedding' => '1 Extra Large Double Bed', 'includes_breakfast' => true,
                'image' => '/images/rooms/room-card.png', 'sort_order' => 3,
            ],
            [
                'slug' => 'deluxe-twin-room', 'name' => 'Deluxe Twin Room', 'rate_per_night' => 950000,
                'currency' => 'Rp', 'size_sqm' => 30, 'max_guests' => 5,
                'bedding' => '2 Single Beds', 'includes_breakfast' => true,
                'image' => '/images/rooms/room-card.png', 'sort_order' => 4,
            ],
            [
                'slug' => 'triple-room-tv', 'name' => 'Triple Room With TV', 'rate_per_night' => 900000,
                'currency' => 'Rp', 'size_sqm' => 33, 'max_guests' => 5,
                'bedding' => '1 Large Double Bed + 1 Single Bed, OR 3 Single Beds', 'includes_breakfast' => true,
                'image' => '/images/rooms/room-card.png', 'sort_order' => 5,
            ],
            [
                'slug' => 'triple-room-pool-view', 'name' => 'Triple Room With Pool View', 'rate_per_night' => 900000,
                'currency' => 'Rp', 'size_sqm' => 33, 'max_guests' => 5,
                'bedding' => '1 Large Double Bed + 1 Single Bed', 'includes_breakfast' => true,
                'image' => '/images/rooms/room-card.png', 'sort_order' => 6,
            ],
        ];

        foreach ($rooms as $room) {
            Room::updateOrCreate(['slug' => $room['slug']], $room);
        }

        $familyRoom = Room::updateOrCreate(
            ['slug' => 'family-room'],
            [
                'name' => 'Family Room',
                'rate_per_night' => 700000,
                'currency' => 'Rp',
                'size_sqm' => 24,
                'max_guests' => 5,
                'bedding' => '1 Large Double Bed OR 2 Single Beds',
                'includes_breakfast' => true,
                'image' => '/images/rooms/family-room-main.png',
                'is_featured' => true,
                'hotel_information' => 'Each room at the hotel is equipped with a wardrobe. In addition to a private bathroom with a shower and complimentary toiletries, rooms at Amed Café & Hotel Kebun Wayan also offer free WiFi. Some rooms feature sea views. All units are fitted with air conditioning and a work desk. A daily breakfast is served with buffet.',
                'sort_order' => 7,
            ]
        );

        $detailImages = [
            ['image' => '/images/rooms/family-room-detail.png', 'alt_text' => 'Bedside table with a lamp and fresh flowers in the Family Room', 'sort_order' => 1],
            ['image' => '/images/rooms/family-room-detail.png', 'alt_text' => 'Bedside table with a lamp and fresh flowers in the Family Room', 'sort_order' => 2],
        ];

        foreach ($detailImages as $detailImage) {
            RoomImage::updateOrCreate(
                ['room_id' => $familyRoom->id, 'sort_order' => $detailImage['sort_order']],
                $detailImage + ['room_id' => $familyRoom->id]
            );
        }
    }
}
