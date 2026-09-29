<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Dev-only default admin — change this password before any real deployment.
        // updateOrCreate (not factory()->create()) so re-running db:seed is safe.
        User::updateOrCreate(
            ['email' => 'admin@amedcafe.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        $this->call([
            SettingSeeder::class,
            RoomSeeder::class,
            ActivitySeeder::class,
            DiningVenueSeeder::class,
            TestimonialSeeder::class,
            PageSeeder::class,
            BannerSeeder::class,
            EventSeeder::class,
        ]);
    }
}
