<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomCardMoreInformationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Page::create(['slug' => 'rooms', 'hero_title' => 'Rooms', 'intro_heading' => 'Rooms', 'intro_body' => 'Body.']);
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
            'includes_breakfast' => true,
        ], $overrides));
    }

    // ---- "Includes Breakfast" is gone ----

    public function test_room_cards_no_longer_render_includes_breakfast(): void
    {
        $this->makeRoom(['name' => 'Breakfast Room', 'includes_breakfast' => true]);
        $this->makeRoom(['name' => 'No Breakfast Room', 'includes_breakfast' => false]);

        $content = $this->get('/room')->assertOk()->getContent();

        $this->assertStringNotContainsString('Includes Breakfast', $content);
    }

    // ---- "More Information" link on room cards ----

    public function test_room_card_renders_more_information_rule_link_with_correct_href_and_accessible_name(): void
    {
        $room = $this->makeRoom(['name' => 'Deluxe Room Pool View']);

        $content = $this->get('/room')->getContent();

        preg_match('/<a[^>]*aria-label="More information about Deluxe Room Pool View"[^>]*>/', $content, $link);
        $this->assertNotEmpty($link, 'More Information link not found');
        $this->assertStringContainsString('class="rule-link"', $link[0]);
        $this->assertStringContainsString('href="'.route('room.detail', ['room' => $room->slug]).'"', $link[0]);
        $this->assertStringNotContainsString('target="_blank"', $link[0]);
    }

    public function test_more_information_shown_regardless_of_includes_breakfast(): void
    {
        $this->makeRoom(['name' => 'No Breakfast Here', 'includes_breakfast' => false]);

        $content = $this->get('/room')->getContent();

        $this->assertStringContainsString('aria-label="More information about No Breakfast Here"', $content);
    }

    public function test_more_information_link_carries_same_search_params_as_title_link_when_flag_on(): void
    {
        config(['booking.enabled' => true]);
        $room = $this->makeRoom(['name' => 'Search Params Room']);

        $content = $this->get('/room?check_in=2026-12-01&check_out=2026-12-05&guest=2')->getContent();

        $expectedUrl = route('room.detail', [
            'room' => $room->slug,
            'check_in' => '2026-12-01',
            'check_out' => '2026-12-05',
            'guest' => '2',
        ]);

        preg_match('/<h3 class="room-card-name"><a href="([^"]*)">Search Params Room<\/a><\/h3>/', $content, $titleLink);
        preg_match('/<a[^>]*aria-label="More information about Search Params Room"[^>]*>/', $content, $moreInfoLink);

        $this->assertNotEmpty($titleLink);
        $this->assertNotEmpty($moreInfoLink);
        $this->assertSame($expectedUrl, html_entity_decode($titleLink[1]));
        $this->assertStringContainsString('href="'.$expectedUrl.'"', html_entity_decode($moreInfoLink[0]));
    }

    public function test_more_information_link_has_no_search_params_when_flag_off_even_if_present_in_query(): void
    {
        config(['booking.enabled' => false]);
        $room = $this->makeRoom(['name' => 'Flag Off Room']);

        $content = $this->get('/room?check_in=2026-12-01&check_out=2026-12-05&guest=2')->getContent();

        preg_match('/<a[^>]*aria-label="More information about Flag Off Room"[^>]*>/', $content, $link);
        $this->assertNotEmpty($link);
        $this->assertStringContainsString('href="'.route('room.detail', ['room' => $room->slug]).'"', $link[0]);
        $this->assertStringNotContainsString('check_in', $link[0]);
    }

    // ---- Family Room section ----

    public function test_family_room_more_information_uses_rule_link_and_has_no_hardcoded_breakfast_line(): void
    {
        $room = $this->makeRoom(['name' => 'Family Room', 'is_featured' => true, 'hotel_information' => 'Info.']);

        $content = $this->get('/room')->getContent();

        $this->assertStringNotContainsString('Include: Breakfast', $content);

        preg_match('/<a[^>]*aria-label="More information about Family Room"[^>]*>/', $content, $link);
        $this->assertNotEmpty($link, 'Family Room More Information link not found');
        $this->assertStringContainsString('class="rule-link"', $link[0]);
        $this->assertStringContainsString('href="'.route('room.detail', ['room' => $room->slug]).'"', $link[0]);
    }

    // ---- Other card elements unchanged ----

    public function test_book_button_ota_row_and_price_badge_still_render(): void
    {
        \App\Models\Setting::updateOrCreate(['key' => 'expedia_url'], ['value' => 'https://www.expedia.com/x']);
        $this->makeRoom(['name' => 'Unaffected Room']);

        $content = $this->get('/room')->getContent();

        $this->assertStringContainsString('room-card-book', $content);
        $this->assertStringContainsString('room-card-ota-row', $content);
        $this->assertStringContainsString('price-badge room-card-badge', $content);
    }
}
