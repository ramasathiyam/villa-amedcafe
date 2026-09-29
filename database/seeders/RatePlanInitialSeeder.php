<?php

namespace Database\Seeders;

use App\Models\Room;
use Illuminate\Database\Seeder;

/**
 * The real rate plans (package names, terms, discounted pricing) aren't known yet. This
 * creates exactly one rate plan per existing room, derived only from that room's own
 * existing data — no invented plans, discounts, or descriptions. Idempotent (uses
 * updateOrCreate, keyed on room_id + name) — safe to re-run, but meant to run once now,
 * not as part of routine seeding (not added to DatabaseSeeder).
 */
class RatePlanInitialSeeder extends Seeder
{
    public function run(): void
    {
        Room::all()->each(function (Room $room) {
            $name = $room->includes_breakfast ? 'Breakfast Included' : 'Room Only';

            $room->ratePlans()->updateOrCreate(
                ['name' => $name],
                [
                    'max_guests' => $room->max_guests,
                    'price_per_night' => $room->rate_per_night,
                    'compare_at_price_per_night' => null,
                    'includes_breakfast' => $room->includes_breakfast,
                    'description' => null,
                    'is_active' => true,
                    'sort_order' => 0,
                ]
            );
        });
    }
}
