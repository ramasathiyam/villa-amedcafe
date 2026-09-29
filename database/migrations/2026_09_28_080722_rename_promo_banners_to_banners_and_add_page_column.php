<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The original create_promo_banners_table migration already ran, so per project
     * convention this is a new migration rather than an edit to that one. "Promo Banner"
     * becomes the generic "Banner", and the single sitewide row becomes one row per page —
     * `page` is added with a default of 'home' so the existing row (whatever an admin has
     * already edited it to say) lands there without a separate data step, then a unique
     * index is added once every row has a real value. No ->change() (doctrine/dbal isn't
     * installed), so the column is added correctly-typed from the start instead of altered.
     */
    public function up(): void
    {
        Schema::rename('promo_banners', 'banners');

        Schema::table('banners', function (Blueprint $table) {
            $table->string('page')->default('home')->after('id');
        });

        Schema::table('banners', function (Blueprint $table) {
            $table->unique('page');
        });
    }

    public function down(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->dropUnique(['page']);
            $table->dropColumn('page');
        });

        Schema::rename('banners', 'promo_banners');
    }
};
