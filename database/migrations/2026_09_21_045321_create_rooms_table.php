<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Room TYPE, not a physical unit (see project notes) — availability/booking will
     * introduce physical units + bookings in a later phase. No `status` column here on
     * purpose: never model booked/available state directly on the room type.
     *
     * `is_featured` + `hotel_information` exist only for the single "Family Room" entry
     * that renders the extra composite section on the Room page (design/ROOM.png) — every
     * other room ignores both columns. Its extra photos live in room_images, not here.
     */
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->unsignedInteger('rate_per_night');
            $table->string('currency')->default('Rp');
            $table->unsignedInteger('size_sqm');
            $table->unsignedInteger('max_guests');
            $table->string('bedding');
            $table->boolean('includes_breakfast')->default(false);
            $table->string('image');
            $table->boolean('is_featured')->default(false);
            $table->text('hotel_information')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
