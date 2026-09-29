<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomListingSearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Booking Feature Flag defaults to off; this suite exercises the search/
        // availability flow itself, so it must explicitly turn it on.
        config(['booking.enabled' => true]);

        Page::create([
            'slug' => 'rooms',
            'hero_title' => 'Rooms',
            'intro_heading' => 'Rooms',
            'intro_body' => 'Test body.',
        ]);
    }

    private function makeRoom(array $overrides = []): Room
    {
        return Room::create(array_merge([
            'slug' => 'test-room-'.uniqid(),
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

    public function test_valid_search_shows_availability_labels(): void
    {
        $this->makeRoom(['name' => 'Ocean View Room']);
        $checkIn = Carbon::now('Asia/Makassar')->addDay()->toDateString();
        $checkOut = Carbon::now('Asia/Makassar')->addDays(4)->toDateString();

        $response = $this->get("/room?check_in={$checkIn}&check_out={$checkOut}");

        $response->assertOk();
        $response->assertSee('3 nights', false);
        $response->assertSee('5 rooms left', false);
    }

    public function test_invalid_dates_show_error_and_no_labels(): void
    {
        $this->makeRoom();
        $date = Carbon::now('Asia/Makassar')->addDay()->toDateString();

        $response = $this->get("/room?check_in={$date}&check_out={$date}");

        $response->assertOk();
        $response->assertSee('Check-out date must be after check-in date.');
        $response->assertDontSee('room-detail-availability', false);
    }

    /**
     * Stage 3b decision: /room no longer hides rooms below the requested guest count —
     * every room stays visible, with its own capacity text and (when short) a minimum-
     * units-needed hint instead.
     */
    public function test_guest_count_shows_capacity_and_minimum_units_hint_instead_of_hiding(): void
    {
        $this->makeRoom(['name' => 'Small Room', 'max_guests' => 2]);
        $this->makeRoom(['name' => 'Big Room', 'max_guests' => 6]);

        $response = $this->get('/room?guest=4');

        $response->assertOk();
        $response->assertSee('Small Room');
        $response->assertSee('Big Room');
        $response->assertSee('Max 2 guests per room');
        $response->assertSee('Max 6 guests per room');
        $response->assertSee('4 guests need at least 2 rooms');
    }

    public function test_room_card_links_carry_search_dates(): void
    {
        $room = $this->makeRoom();
        $checkIn = Carbon::now('Asia/Makassar')->addDay()->toDateString();
        $checkOut = Carbon::now('Asia/Makassar')->addDays(4)->toDateString();

        $response = $this->get("/room?check_in={$checkIn}&check_out={$checkOut}");

        $response->assertOk();
        $expectedUrl = route('room.detail', [
            'room' => $room->slug,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
        ]);
        $response->assertSee(htmlspecialchars($expectedUrl), false);
    }

    public function test_no_dates_shows_no_availability_labels(): void
    {
        $this->makeRoom();

        $response = $this->get('/room');

        $response->assertOk();
        $response->assertDontSee('room-detail-availability', false);
    }
}
