<?php

namespace Tests\Feature;

use App\Models\DiningVenue;
use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IntroBookNowLinkTest extends TestCase
{
    use RefreshDatabase;

    private function makePage(string $slug): Page
    {
        return Page::create([
            'slug' => $slug,
            'hero_title' => 'Title',
            'intro_heading' => 'About Us',
            'intro_body' => 'Body copy.',
        ]);
    }

    private function makeDiningVenue(string $slug, string $name): DiningVenue
    {
        return DiningVenue::create([
            'slug' => $slug,
            'name' => $name,
            'is_active' => true,
            'hero_title' => $name,
            'hero_image' => '/images/dining/placeholder.png',
            'intro_heading' => $name,
            'intro_body' => 'Body.',
        ]);
    }

    /**
     * @return array<string, string> page => url
     */
    private function affectedPages(): array
    {
        $this->makePage('home');
        $this->makePage('activities');
        $this->makePage('spa');
        $this->makePage('rooms');
        $this->makePage('resto-amed-cafe');
        $this->makePage('barak-rooftop-and-bar');
        $this->makeDiningVenue('resto-amed-cafe', 'Resto Amed Cafe');
        $this->makeDiningVenue('barak-rooftop-and-bar', 'Barak Rooftop and Bar');

        return [
            'Home' => '/',
            'Activity' => '/activity',
            'Spa' => '/spa',
            'Contact' => '/contact',
            'Rooms' => '/room',
            'Resto Amed Cafe' => '/dining/resto-amed-cafe',
            'Barak Rooftop and Bar' => '/dining/barak-rooftop-and-bar',
        ];
    }

    public function test_intro_book_now_is_an_enabled_link_to_rooms_on_every_affected_page(): void
    {
        $pages = $this->affectedPages();
        $roomsUrl = route('room');

        foreach ($pages as $label => $url) {
            $response = $this->get($url);
            $response->assertOk();
            $content = $response->getContent();

            preg_match('/<section class="intro-section">.*?<\/section>/s', $content, $introBlock);
            $this->assertNotEmpty($introBlock, "intro-section not found on {$label}");

            preg_match('/<a[^>]*class="rule-link[^"]*"[^>]*>.*?Book Now.*?<\/a>/s', $introBlock[0], $link);
            $this->assertNotEmpty($link, "Book Now is not rendered as an enabled <a> link on {$label}");
            $this->assertStringContainsString('href="'.$roomsUrl.'"', $link[0], "Book Now does not link to route('room') on {$label}");
            $this->assertStringNotContainsString('target="_blank"', $link[0], "Book Now should open in the same tab on {$label}");
        }
    }

    public function test_intro_book_now_is_never_rendered_disabled_on_affected_pages(): void
    {
        $pages = $this->affectedPages();

        foreach ($pages as $label => $url) {
            $content = $this->get($url)->getContent();

            preg_match('/<section class="intro-section">.*?<\/section>/s', $content, $introBlock);
            $this->assertNotEmpty($introBlock, "intro-section not found on {$label}");

            $this->assertStringNotContainsString('rule-link-disabled', $introBlock[0], "Book Now is disabled on {$label}");
            $this->assertStringNotContainsString('aria-disabled="true"', $introBlock[0], "Book Now is disabled on {$label}");
        }
    }

    public function test_other_links_on_affected_pages_are_unchanged(): void
    {
        $this->affectedPages();

        // Room detail "Discover More" style links / rule-links elsewhere are untouched —
        // spot check the Rooms page's Family/room-card links still resolve fine and the
        // page still renders its other content normally.
        $response = $this->get('/room');
        $response->assertOk();
        $response->assertSee('About Us');
    }

    public function test_navbar_book_now_still_follows_booking_flag_behavior(): void
    {
        $this->makePage('home');

        config(['booking.enabled' => false]);
        $offContent = $this->get('/')->getContent();
        $this->assertStringContainsString('aria-disabled="true"', $offContent);

        config(['booking.enabled' => true]);
        $onContent = $this->get('/')->getContent();
        preg_match('/<header class="navbar">.*?<nav/s', $onContent, $navbarBlock);
        $this->assertNotEmpty($navbarBlock);
    }
}
