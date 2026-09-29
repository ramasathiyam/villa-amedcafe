<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Prices are unsignedInteger, same convention as rooms.rate_per_night (smallest
     * currency unit, no decimals — matches how this project's Rupiah amounts are always
     * stored). No currency column here: currency is a per-room property (rooms.currency),
     * not per-plan, so pricing display always borrows the parent Room's currency.
     */
    public function up(): void
    {
        Schema::create('rate_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('max_guests');
            $table->unsignedInteger('price_per_night');
            $table->unsignedInteger('compare_at_price_per_night')->nullable();
            $table->boolean('includes_breakfast')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rate_plans');
    }
};
