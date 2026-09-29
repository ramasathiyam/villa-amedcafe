<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One reusable table for the small set of public marketing pages that have a
     * hero + intro section (home, rooms, activities, spa) — mirrors dining_venues'
     * shape (hero_title/hero_subtitle/hero_image/intro_heading/intro_body) rather than
     * inventing a new pattern. Slugs must stay exactly 'home'/'rooms'/'activities'/'spa'
     * — PageController looks rows up by these fixed strings, not a dynamic wildcard.
     * Unlike dining_venues, there's no is_active/sort_order: these 4 rows always exist
     * and are never listed/ordered against each other, only edited individually.
     */
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('hero_title');
            $table->string('hero_subtitle')->nullable();
            $table->string('hero_image')->nullable();
            $table->string('intro_heading');
            $table->text('intro_body');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
