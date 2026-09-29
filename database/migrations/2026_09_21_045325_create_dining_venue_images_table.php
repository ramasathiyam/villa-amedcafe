<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * `group` distinguishes which gallery strip / photo slot an image belongs to on a
     * venue's page (e.g. 'gallery_1', 'gallery_2', 'menu_highlight', 'feature') — matches
     * how each venue's Blade view currently renders multiple distinct photo strips.
     * `title`/`description` are only used by Barak's "menu highlight" captions.
     */
    public function up(): void
    {
        Schema::create('dining_venue_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dining_venue_id')->constrained()->cascadeOnDelete();
            $table->string('image');
            $table->string('group');
            $table->string('title')->nullable();
            $table->string('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dining_venue_images');
    }
};
