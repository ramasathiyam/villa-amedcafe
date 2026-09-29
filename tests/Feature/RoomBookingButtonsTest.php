<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Room;
use App\Models\Setting;
use App\Models\User;
use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RoomBookingButtonsTest extends TestCase
{
    use RefreshDatabase;

    private string $otaLogoDir;

    protected function setUp(): void
    {
        parent::setUp();

        Page::create(['slug' => 'rooms', 'hero_title' => 'Rooms', 'intro_heading' => 'Rooms', 'intro_body' => 'Body.']);

        // PageController checks config('ota.logo_path') for the OTA logo files, not a
        // hardcoded public_path() — pointing it at a throwaway temp directory here means
        // these tests never read, write, move, or delete anything under the real public/
        // directory. Tests that want a logo "present" write a dummy file into this
        // directory; tests that want it "missing" simply don't.
        $this->otaLogoDir = sys_get_temp_dir().'/ota-logo-test-'.uniqid();
        File::ensureDirectoryExists($this->otaLogoDir);
        config(['ota.logo_path' => $this->otaLogoDir]);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->otaLogoDir);

        parent::tearDown();
    }

    private function makeRoom(array $overrides = []): Room
    {
        return Room::create(array_merge([
            'name' => 'Test Room',
            'rate_per_night' => 700000,
            'currency' => 'Rp',
            'size_sqm' => 24,
            'max_guests' => 2,
            'bedding' => '1 Double Bed',
            'image' => '/images/rooms/room-card.png',
            'is_active' => true,
            'total_units' => 5,
        ], $overrides));
    }

    private function setSetting(string $key, ?string $value): void
    {
        Setting::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    private function admin(): User
    {
        return User::create([
            'name' => 'Admin', 'email' => 'admin-'.uniqid().'@example.com',
            'password' => Hash::make('password'), 'role' => 'admin',
        ]);
    }

    private function putFakeLogo(string $filename): void
    {
        File::put($this->otaLogoDir.'/'.$filename, 'fake-logo-content');
    }

    /**
     * Asserts the "Book {roomName}" link exists and has no <a> ancestor, using a real DOM
     * parse (not string/regex matching) so this is robust to markup restructuring.
     */
    private function assertBookButtonNotNestedInLink(string $html, string $roomName): void
    {
        $dom = new DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new DOMXPath($dom);
        $nodes = $xpath->query("//a[@aria-label='Book {$roomName}']");

        $this->assertGreaterThan(0, $nodes->length, "BOOK link not found for {$roomName}");

        foreach ($nodes as $node) {
            $parent = $node->parentNode;
            while ($parent) {
                $this->assertNotSame('a', strtolower($parent->nodeName ?? ''), "BOOK link nested inside another <a> for {$roomName}");
                $parent = $parent->parentNode;
            }
        }
    }

    // ---- BOOK is on the photo, not in the OTA row ----

    public function test_book_renders_on_photo_area_not_in_ota_row_on_room_card(): void
    {
        $this->setSetting('expedia_url', 'https://www.expedia.com/x');
        $this->makeRoom(['name' => 'Photo Book Room']);

        $content = $this->get('/room')->assertOk()->getContent();

        preg_match('/<div class="room-card-image-wrap">.*?<\/div>\s*<div class="room-card-body">/s', $content, $imageWrap);
        $this->assertNotEmpty($imageWrap, 'room-card-image-wrap block not found');
        $this->assertStringContainsString('room-card-book', $imageWrap[0]);
        $this->assertStringContainsString('aria-label="Book Photo Book Room"', $imageWrap[0]);

        preg_match('/<div class="room-card-ota-row">.*?<\/div>/s', $content, $otaRow);
        $this->assertNotEmpty($otaRow, 'room-card-ota-row block not found');
        $this->assertStringNotContainsString('room-card-book', $otaRow[0]);
        $this->assertStringNotContainsString('>BOOK<', $otaRow[0]);
    }

    public function test_book_renders_on_family_room_photo_not_in_ota_row(): void
    {
        $this->setSetting('expedia_url', 'https://www.expedia.com/x');
        $this->makeRoom(['name' => 'Family Photo Room', 'is_featured' => true, 'hotel_information' => 'Info.']);

        $content = $this->get('/room')->getContent();

        preg_match('/<div class="room-family-photo">.*?<div class="room-family-detail-col">/s', $content, $photoBlock);
        $this->assertNotEmpty($photoBlock, 'room-family-photo block not found');
        $this->assertStringContainsString('room-family-book', $photoBlock[0]);
        $this->assertStringContainsString('aria-label="Book Family Photo Room"', $photoBlock[0]);

        preg_match('/<div class="room-family-ota-row">.*?<\/div>/s', $content, $otaRow);
        $this->assertNotEmpty($otaRow, 'room-family-ota-row block not found');
        $this->assertStringNotContainsString('room-family-book', $otaRow[0]);
        $this->assertStringNotContainsString('>BOOK<', $otaRow[0]);
    }

    // ---- BOOK not nested inside another <a> ----

    public function test_book_button_not_nested_inside_another_link_on_room_card(): void
    {
        $this->makeRoom(['name' => 'Nesting Check Room']);

        $content = $this->get('/room')->getContent();

        $this->assertBookButtonNotNestedInLink($content, 'Nesting Check Room');
    }

    public function test_book_button_not_nested_inside_another_link_on_family_room(): void
    {
        $this->makeRoom(['name' => 'Family Nesting Room', 'is_featured' => true, 'hotel_information' => 'Info.']);

        $content = $this->get('/room')->getContent();

        $this->assertBookButtonNotNestedInLink($content, 'Family Nesting Room');
    }

    // ---- BOOK behavior (unchanged from before) ----

    public function test_book_links_to_whatsapp_with_encoded_room_name_when_flag_off(): void
    {
        config(['booking.enabled' => false]);
        $this->setSetting('whatsapp', '087715021995');
        $this->makeRoom(['name' => 'Enquiry Room']);

        $content = $this->get('/room')->getContent();

        $this->assertStringContainsString('https://wa.me/6287715021995', $content);
        $this->assertStringContainsString(rawurlencode('Hello, I am interested in the Enquiry Room.'), $content);
    }

    public function test_book_falls_back_to_contact_when_whatsapp_empty(): void
    {
        config(['booking.enabled' => false]);
        $this->setSetting('whatsapp', '');
        $this->makeRoom(['name' => 'No Whatsapp Room']);

        $content = $this->get('/room')->getContent();

        preg_match('/<a[^>]*aria-label="Book No Whatsapp Room"[^>]*>/', $content, $bookTag);
        $this->assertNotEmpty($bookTag);
        $this->assertStringContainsString('href="'.route('contact').'"', $bookTag[0]);
        $this->assertStringNotContainsString('wa.me', $bookTag[0]);
    }

    public function test_book_links_to_detail_page_when_flag_on(): void
    {
        config(['booking.enabled' => true]);
        $room = $this->makeRoom(['name' => 'Flag On Room']);

        $content = $this->get('/room')->getContent();

        preg_match('/<a[^>]*aria-label="Book Flag On Room"[^>]*>/', $content, $bookTag);
        $this->assertNotEmpty($bookTag);
        $this->assertStringContainsString('href="'.route('room.detail', ['room' => $room->slug]).'"', $bookTag[0]);
    }

    // ---- OTA row order: Expedia, Booking.com, Agoda ----

    public function test_ota_row_order_is_expedia_booking_com_agoda(): void
    {
        $this->setSetting('expedia_url', 'https://www.expedia.com/x');
        $this->setSetting('booking_com_url', 'https://www.booking.com/x');
        $this->setSetting('agoda_url', 'https://www.agoda.com/x');
        $this->makeRoom(['name' => 'Order Room']);
        $this->makeRoom(['name' => 'Order Featured Room', 'is_featured' => true, 'hotel_information' => 'Info.']);

        $content = $this->get('/room')->getContent();

        foreach (['Order Room', 'Order Featured Room'] as $name) {
            $expediaPos = strpos($content, "Book {$name} on Expedia");
            $bookingPos = strpos($content, "Book {$name} on Booking.com");
            $agodaPos = strpos($content, "Book {$name} on Agoda");

            $this->assertNotFalse($expediaPos, "Expedia button missing for {$name}");
            $this->assertNotFalse($bookingPos, "Booking.com button missing for {$name}");
            $this->assertNotFalse($agodaPos, "Agoda button missing for {$name}");
            $this->assertTrue($expediaPos < $bookingPos && $bookingPos < $agodaPos, "OTA buttons out of order for {$name}");
        }
    }

    public function test_expedia_and_booking_com_links_use_site_settings_and_open_new_tab(): void
    {
        $this->setSetting('expedia_url', 'https://www.expedia.com/Karangasem-Hotels-Amed-Cafe-Hotel-Kebun-Wayan.h4821901.Hotel-Information');
        $this->setSetting('booking_com_url', 'https://www.booking.com/hotel/id/amed-cafe.html');
        $this->makeRoom(['name' => 'Link Room']);

        $content = $this->get('/room')->getContent();

        $this->assertStringContainsString('https://www.expedia.com/Karangasem-Hotels-Amed-Cafe-Hotel-Kebun-Wayan.h4821901.Hotel-Information', $content);
        $this->assertStringContainsString('https://www.booking.com/hotel/id/amed-cafe.html', $content);

        preg_match('/<a[^>]*href="https:\/\/www\.expedia\.com[^>]*>/', $content, $expediaTag);
        preg_match('/<a[^>]*href="https:\/\/www\.booking\.com[^>]*>/', $content, $bookingTag);

        $this->assertStringContainsString('target="_blank"', $expediaTag[0] ?? '');
        $this->assertStringContainsString('rel="noopener noreferrer"', $expediaTag[0] ?? '');
        $this->assertStringContainsString('aria-label="Book Link Room on Expedia (opens in a new tab)"', $expediaTag[0] ?? '');
        $this->assertStringContainsString('target="_blank"', $bookingTag[0] ?? '');
        $this->assertStringContainsString('rel="noopener noreferrer"', $bookingTag[0] ?? '');
    }

    // ---- Empty URL hides only that button ----

    public function test_empty_expedia_url_hides_only_expedia_button(): void
    {
        $this->setSetting('expedia_url', null);
        $this->setSetting('booking_com_url', 'https://www.booking.com/x');
        $this->makeRoom(['name' => 'No Expedia Room']);

        $content = $this->get('/room')->getContent();

        $this->assertStringNotContainsString('on Expedia', $content);
        $this->assertStringContainsString('on Booking.com', $content);
    }

    public function test_empty_booking_com_url_hides_only_booking_com_button(): void
    {
        $this->setSetting('expedia_url', 'https://www.expedia.com/x');
        $this->setSetting('booking_com_url', null);
        $this->makeRoom(['name' => 'No Booking Room']);

        $content = $this->get('/room')->getContent();

        $this->assertStringContainsString('on Expedia', $content);
        $this->assertStringNotContainsString('on Booking.com', $content);
    }

    public function test_empty_agoda_url_hides_only_agoda_button(): void
    {
        $this->setSetting('expedia_url', 'https://www.expedia.com/x');
        $this->setSetting('agoda_url', null);
        $this->makeRoom(['name' => 'No Agoda Only Room']);

        $content = $this->get('/room')->getContent();

        $this->assertStringContainsString('on Expedia', $content);
        $this->assertStringNotContainsString('on Agoda', $content);
    }

    public function test_all_three_ota_urls_empty_renders_no_row_markup(): void
    {
        $this->setSetting('expedia_url', null);
        $this->setSetting('booking_com_url', null);
        $this->setSetting('agoda_url', null);
        $this->makeRoom(['name' => 'No OTA Room']);

        $content = $this->get('/room')->getContent();

        $this->assertStringNotContainsString('room-card-ota-row', $content);
        // BOOK on the photo must still render even with no OTA buttons.
        $this->assertStringContainsString('aria-label="Book No OTA Room"', $content);
    }

    // ---- Agoda logo / text fallback ----

    public function test_agoda_logo_renders_when_file_exists(): void
    {
        $this->setSetting('agoda_url', 'https://www.agoda.com/x');
        $this->putFakeLogo('agoda.png');
        $this->makeRoom(['name' => 'Agoda Logo Room']);

        $content = $this->get('/room')->getContent();

        $this->assertStringContainsString('images/ota/agoda.png', $content);
        $this->assertStringNotContainsString('>Agoda</span>', $content);
    }

    public function test_agoda_shows_text_when_logo_file_missing(): void
    {
        $this->setSetting('agoda_url', 'https://www.agoda.com/x');
        $this->makeRoom(['name' => 'Agoda Text Room']);

        $content = $this->get('/room')->getContent();

        $this->assertStringContainsString('room-card-ota-text" aria-hidden="true">Agoda</span>', $content);
        $this->assertStringNotContainsString('images/ota/agoda.png', $content);
    }

    public function test_expedia_and_booking_com_logo_fallback_still_work(): void
    {
        $this->setSetting('expedia_url', 'https://www.expedia.com/x');
        $this->setSetting('booking_com_url', 'https://www.booking.com/x');
        $this->putFakeLogo('expedia.png');
        $this->putFakeLogo('booking-com.png');
        $this->makeRoom(['name' => 'Logo Room']);

        $content = $this->get('/room')->getContent();

        $this->assertStringContainsString('images/ota/expedia.png', $content);
        $this->assertStringContainsString('images/ota/booking-com.png', $content);
        $this->assertStringNotContainsString('>Expedia</span>', $content);
        $this->assertStringNotContainsString('>Booking.com</span>', $content);
    }

    // ---- Removed markup (still true) ----

    public function test_no_whatsapp_icon_remains_on_card_photo(): void
    {
        $this->makeRoom(['name' => 'Clean Card Room']);

        $content = $this->get('/room')->getContent();

        $this->assertStringNotContainsString('room-card-whatsapp', $content);
    }

    public function test_no_bird_or_tiket_buttons_remain(): void
    {
        $this->makeRoom(['name' => 'Old Logos Room']);

        $content = $this->get('/room')->getContent();

        $this->assertStringNotContainsString('ota-traveloka', $content);
        $this->assertStringNotContainsString('ota-tiket', $content);
    }

    // ---- Admin Site Settings ----

    public function test_admin_site_settings_shows_agoda_url_field(): void
    {
        $admin = $this->admin();
        $this->setSetting('agoda_url', 'https://www.agoda.com/existing');

        $response = $this->actingAs($admin)->get('/admin/settings');

        $response->assertOk();
        $response->assertSee('Agoda URL', false);
        $response->assertSee('name="agoda_url"', false);
        $response->assertSee('https://www.agoda.com/existing', false);
        $response->assertSee('Leave empty to hide this button.', false);
    }

    public function test_admin_can_save_agoda_url(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)->put('/admin/settings', [
            'phone' => '(0363) 23473',
            'whatsapp' => '087715021995',
            'address' => 'Test address',
            'agoda_url' => 'https://www.agoda.com/example',
        ]);

        $response->assertRedirect(route('admin.settings.edit'));
        $this->assertSame('https://www.agoda.com/example', Setting::get('agoda_url'));
    }

    public function test_invalid_agoda_url_is_rejected(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->put('/admin/settings', [
            'phone' => '(0363) 23473',
            'whatsapp' => '087715021995',
            'address' => 'Test address',
            'agoda_url' => 'not a url',
        ])->assertSessionHasErrors(['agoda_url']);

        $this->actingAs($admin)->put('/admin/settings', [
            'phone' => '(0363) 23473',
            'whatsapp' => '087715021995',
            'address' => 'Test address',
            'agoda_url' => 'http://insecure.example.com',
        ])->assertSessionHasErrors(['agoda_url']);
    }

    // ---- Query efficiency ----

    public function test_settings_are_not_queried_once_per_room_card(): void
    {
        $this->setSetting('expedia_url', 'https://www.expedia.com/x');
        $this->setSetting('booking_com_url', 'https://www.booking.com/x');
        $this->setSetting('agoda_url', 'https://www.agoda.com/x');
        $this->setSetting('whatsapp', '087715021995');

        $count = 0;
        DB::listen(function ($query) use (&$count) {
            if (str_contains($query->sql, 'settings')) {
                $count++;
            }
        });

        $countSettingsQueriesFor = function (int $roomCount) use (&$count): int {
            Room::query()->delete();
            for ($i = 0; $i < $roomCount; $i++) {
                $this->makeRoom(['name' => 'Query Room '.$i.'-'.$roomCount]);
            }

            $count = 0;
            $this->get('/room')->assertOk();

            return $count;
        };

        // Page-level settings (navbar, footer, room()'s expedia/booking/agoda/whatsapp
        // reads) are fixed overhead unrelated to how many rooms exist — if any OTA URL
        // were queried per card instead of once, this count would grow with the room
        // count. It must not.
        $queriesWithOneRoom = $countSettingsQueriesFor(1);
        $queriesWithFiveRooms = $countSettingsQueriesFor(5);

        $this->assertSame($queriesWithOneRoom, $queriesWithFiveRooms);
    }
}
