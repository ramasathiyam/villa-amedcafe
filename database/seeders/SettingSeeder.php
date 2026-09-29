<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * Sitewide contact data currently duplicated across Footer, Contact page, and RoomCard's
 * WhatsApp link (resources/views/components/footer.blade.php, contact/index.blade.php,
 * cards/room-card.blade.php). Values transcribed verbatim from those files.
 */
class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'phone' => '(0363) 23473',
            'whatsapp' => '087715021995',
            'email' => 'info@amedcafe.com',
            'address' => 'Amed Café & Hotel Kebun Wayan, Jl. Raya Amed, Karangasem, Indonesia 80852',
            'google_maps_url' => 'https://maps.app.goo.gl/MGx11Qekuz8wZY7k9',
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        // OTA links: seeded once as a starting value, but never overwritten on a re-run —
        // unlike the block above, an admin-edited (or intentionally emptied) URL here must
        // survive `db:seed`.
        $otaDefaults = [
            'expedia_url' => 'https://www.expedia.com/Karangasem-Hotels-Amed-Cafe-Hotel-Kebun-Wayan.h4821901.Hotel-Information',
            'booking_com_url' => 'https://www.booking.com/hotel/id/amed-cafe.html',
            'agoda_url' => 'https://www.agoda.com/amed-cafe-bungalow/hotel/bali-id.html',
        ];

        foreach ($otaDefaults as $key => $value) {
            Setting::firstOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
