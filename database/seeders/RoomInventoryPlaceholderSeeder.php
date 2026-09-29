<?php

namespace Database\Seeders;

use App\Models\Room;
use Illuminate\Database\Seeder;

/**
 * The real per-room unit counts aren't known yet. This sets every existing room to a
 * single placeholder value (5) so availability has something non-zero to calculate
 * against until the real counts are entered via Admin's new "Total Units" field before
 * launch. Idempotent (safe to re-run) — but re-running will overwrite any real values an
 * admin has since entered, so this is meant to run once now, not as part of routine
 * seeding.
 */
class RoomInventoryPlaceholderSeeder extends Seeder
{
    private const PLACEHOLDER_UNITS = 5;

    public function run(): void
    {
        Room::query()->update(['total_units' => self::PLACEHOLDER_UNITS]);
    }
}
