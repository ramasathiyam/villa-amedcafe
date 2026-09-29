<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * display_from/display_until are the DISPLAY window (when the banner shows on Home),
     * not the event's own date — null on either side means unlimited in that direction.
     * See App\Models\Event::scopeVisibleOnHome().
     */
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->string('title');
            $table->text('description');
            $table->string('background_image');
            $table->string('link_url')->nullable();
            $table->date('display_from')->nullable();
            $table->date('display_until')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
