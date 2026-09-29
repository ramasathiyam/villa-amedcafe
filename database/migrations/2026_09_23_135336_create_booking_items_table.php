<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One row per room/date-range/unit-count within a booking. check_out is the
     * departure day, not a night — a room is occupied each night from check_in up to but
     * excluding check_out (see AvailabilityService). room_id restricts delete (not
     * cascade) so a room with booking history can't be removed out from under it; Admin's
     * RoomController::destroy() now catches that and shows a clear message instead of a
     * 500. No guest/price/rate-plan columns yet — Stage 3+ extends this table.
     */
    public function up(): void
    {
        Schema::create('booking_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('room_id')->constrained()->restrictOnDelete();
            $table->date('check_in');
            $table->date('check_out');
            $table->unsignedSmallInteger('units');
            $table->timestamps();

            $table->index(['room_id', 'check_in', 'check_out']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_items');
    }
};
