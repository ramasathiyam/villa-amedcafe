<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Data migration only (settings is a flat key/value table, no schema change). Renames the
 * agoda_url row to expedia_url in place, preserving the row so the settings count doesn't
 * change. Naturally idempotent: a second run finds no row with key='agoda_url' left to
 * rename and updates zero rows. Safe on a fresh database, where this migration runs before
 * any seeder and simply finds nothing to rename — SettingSeeder seeds expedia_url directly.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('settings')->where('key', 'agoda_url')->update([
            'key' => 'expedia_url',
            'value' => 'https://www.expedia.com/Karangasem-Hotels-Amed-Cafe-Hotel-Kebun-Wayan.h4821901.Hotel-Information',
        ]);
    }

    public function down(): void
    {
        DB::table('settings')->where('key', 'expedia_url')->update([
            'key' => 'agoda_url',
            'value' => 'https://www.agoda.com/amed-cafe-bungalow/hotel/bali-id.html',
        ]);
    }
};
