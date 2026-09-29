<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Slugs must stay exactly 'resto-amed-cafe' and 'barak-rooftop-and-bar' — the public
     * routes in routes/web.php are fixed named routes keyed off these same strings, not a
     * dynamic {venue:slug} wildcard, so the existing URLs never change.
     */
    public function up(): void
    {
        Schema::create('dining_venues', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('hero_title');
            $table->string('hero_subtitle')->nullable();
            $table->string('hero_image');
            $table->string('intro_heading');
            $table->text('intro_body');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dining_venues');
    }
};
