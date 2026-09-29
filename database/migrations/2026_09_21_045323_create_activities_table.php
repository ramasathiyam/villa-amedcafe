<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One table serves both the Home page's teaser cards and the Activity page's
     * "Exceptional Experiences" grid — they were two disjoint hardcoded PHP arrays with
     * the same shape, not two real content types, so the two boolean flags below pick
     * where each row appears instead of duplicating the schema.
     *
     * `price_label` stays free text (not numeric price + unit) — the current data mixes
     * formats ("Rp700.000/Pax", "Rp1.200.000/2 Pax") that a numeric column can't represent
     * without changing how PriceBadge is called in Blade.
     */
    public function up(): void
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('price_label')->nullable();
            $table->json('includes')->nullable();
            $table->string('image')->nullable();
            $table->boolean('show_on_home')->default(false);
            $table->boolean('show_on_activity_page')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
