<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Preserves the descriptive per-image alt text currently hardcoded in the Dining
     * Blade views (e.g. "Grilled chicken wings") — distinct from `title`/`description`,
     * which are visible on-page captions (only populated for Barak's menu_highlight
     * photos). Mirrors the room_images.alt_text precedent from the Rooms CRUD phase.
     */
    public function up(): void
    {
        Schema::table('dining_venue_images', function (Blueprint $table) {
            $table->string('alt_text')->nullable()->after('image');
        });
    }

    public function down(): void
    {
        Schema::table('dining_venue_images', function (Blueprint $table) {
            $table->dropColumn('alt_text');
        });
    }
};
