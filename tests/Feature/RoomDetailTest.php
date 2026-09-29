<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Room;
use App\Models\RoomImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomDetailTest extends TestCase
{
    use RefreshDatabase;

    private function makeRoom(array $overrides = []): Room
    {
        return Room::create(array_merge([
            'slug' => 'test-room',
            'name' => 'Test Room',
            'rate_per_night' => 700000,
            'currency' => 'Rp',
            'size_sqm' => 24,
            'max_guests' => 2,
            'bedding' => '1 Double Bed',
            'includes_breakfast' => true,
            'image' => '/images/rooms/room-card.png',
            'is_active' => true,
        ], $overrides));
    }

    public function test_detail_page_returns_200_and_shows_name_and_price(): void
    {
        $room = $this->makeRoom();

        $response = $this->get('/room/'.$room->slug);

        $response->assertOk();
        $response->assertSee('Test Room');
        $response->assertSee('Rp700,000', false);
    }

    public function test_unknown_slug_returns_404(): void
    {
        $this->get('/room/does-not-exist')->assertNotFound();
    }

    public function test_inactive_room_returns_404(): void
    {
        $room = $this->makeRoom(['slug' => 'inactive-room', 'is_active' => false]);

        $this->get('/room/'.$room->slug)->assertNotFound();
    }

    public function test_room_with_one_image_renders_without_slider_controls(): void
    {
        $room = $this->makeRoom(['slug' => 'single-image-room']);

        $response = $this->get('/room/'.$room->slug);

        $response->assertOk();
        $response->assertDontSee('data-hero-slider', false);
        $response->assertDontSee('hero-slider-arrow', false);
        $response->assertDontSee('hero-slider-dots', false);
    }

    public function test_room_with_multiple_images_renders_slider_controls(): void
    {
        $room = $this->makeRoom(['slug' => 'multi-image-room']);
        RoomImage::create(['room_id' => $room->id, 'image' => '/images/rooms/family-room-detail.png', 'sort_order' => 0]);

        $response = $this->get('/room/'.$room->slug);

        $response->assertOk();
        $response->assertSee('data-hero-slider', false);
        $response->assertSee('hero-slider-arrow', false);
        $response->assertSee('hero-slider-dots', false);
    }

    public function test_room_index_links_to_detail_pages(): void
    {
        Page::create([
            'slug' => 'rooms',
            'hero_title' => 'Rooms',
            'intro_heading' => 'Rooms',
            'intro_body' => 'Test body.',
        ]);
        $room = $this->makeRoom();

        $response = $this->get('/room');

        $response->assertOk();
        $response->assertSee(route('room.detail', $room->slug), false);
    }
}
