<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * dining_venue_id is nullable so a testimonial can optionally apply sitewide instead
     * of to one specific venue. Not seeded with the current placeholder copy — see
     * TestimonialSeeder.
     */
    public function up(): void
    {
        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dining_venue_id')->nullable()->constrained()->nullOnDelete();
            $table->text('quote');
            $table->string('name');
            $table->unsignedTinyInteger('rating')->default(5);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('testimonials');
    }
};
