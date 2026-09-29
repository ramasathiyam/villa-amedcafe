<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavbarBookNowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Page::create(['slug' => 'home', 'hero_title' => 'Home', 'intro_heading' => 'Home', 'intro_body' => 'Body.']);
    }

    public function test_flag_off_book_now_links_to_whatsapp_with_encoded_message_desktop_and_mobile(): void
    {
        config(['booking.enabled' => false]);
        Setting::updateOrCreate(['key' => 'whatsapp'], ['value' => '081234567890']);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('https://wa.me/6281234567890', false);
        $response->assertSee(rawurlencode('Hello, I would like to make a reservation.'), false);
        // Appears twice: desktop navbar-utility-right and the mobile-panel.
        $response->assertSeeInOrder(['navbar-utility-right', 'wa.me', 'mobile-panel', 'wa.me'], false);
    }

    public function test_flag_off_and_whatsapp_empty_book_now_links_to_contact(): void
    {
        config(['booking.enabled' => false]);
        Setting::updateOrCreate(['key' => 'whatsapp'], ['value' => '']);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertDontSee('wa.me', false);
        $response->assertSee(route('contact'), false);
    }

    public function test_flag_on_keeps_current_disabled_behavior(): void
    {
        config(['booking.enabled' => true]);
        Setting::updateOrCreate(['key' => 'whatsapp'], ['value' => '081234567890']);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Booking flow not defined yet');
        $response->assertDontSee('wa.me', false);
    }

    public function test_no_disabled_book_now_remains_when_flag_is_off(): void
    {
        config(['booking.enabled' => false]);
        Setting::updateOrCreate(['key' => 'whatsapp'], ['value' => '081234567890']);

        $response = $this->get('/');

        $response->assertOk();
        // "Booking flow not defined yet" is also the disabled-reason for other, unrelated
        // pre-existing disabled links elsewhere on the page (e.g. the intro-section's own
        // Book Now) — Part 0 only changes the navbar, so scope the check to <header
        // class="navbar">.
        preg_match('/<header class="navbar">.*?<\/header>/s', $response->getContent(), $matches);
        $this->assertNotEmpty($matches, 'Navbar not found in response.');
        $this->assertStringNotContainsString('Booking flow not defined yet', $matches[0]);
        $this->assertStringNotContainsString('btn-disabled', $matches[0]);
    }
}
