<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Singleton — exactly one row ever exists (seeded once, never created/deleted from
     * Admin). Mirrors the `pages` table's "fixed row, edit-only" shape, not Settings'
     * key/value shape, since this needs an image field.
     */
    public function up(): void
    {
        Schema::create('promo_banners', function (Blueprint $table) {
            $table->id();
            $table->string('eyebrow');
            $table->string('heading');
            $table->text('body');
            $table->string('background_image');
            $table->string('link_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promo_banners');
    }
};
