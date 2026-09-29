<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Generic per-venue menu item (currently only Barak's "Signature Cocktails" use this
     * shape) — kept venue-agnostic so Resto Amed Cafe can use it later without a schema
     * change.
     */
    public function up(): void
    {
        Schema::create('dining_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dining_venue_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dining_items');
    }
};
