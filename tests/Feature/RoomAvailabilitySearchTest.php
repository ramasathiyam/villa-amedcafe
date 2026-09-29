<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RoomAvailabilitySearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Booking Feature Flag defaults to off; this suite exercises the search/
        // availability flow itself, so it must explicitly turn it on.
        config(['booking.enabled' => true]);
    }

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
            'image' => '/images/rooms/room-card.png',
            'is_active' => true,
            'total_units' => 5,
        ], $overrides));
    }

    private function admin(): User
    {
        return User::create([
            'name' => 'Admin',
            'email' => 'admin-'.uniqid().'@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);
    }

    public function test_check_in_in_the_past_shows_validation_error(): void
    {
        $room = $this->makeRoom();
        $yesterday = Carbon::now('Asia/Makassar')->subDay()->toDateString();
        $checkOut = Carbon::now('Asia/Makassar')->addDays(2)->toDateString();

        $response = $this->get("/room/{$room->slug}?check_in={$yesterday}&check_out={$checkOut}");

        $response->assertOk();
        $response->assertSee('Check-in date cannot be in the past.');
    }

    public function test_check_out_before_or_equal_check_in_shows_validation_error(): void
    {
        $room = $this->makeRoom();
        $date = Carbon::now('Asia/Makassar')->addDay()->toDateString();

        $response = $this->get("/room/{$room->slug}?check_in={$date}&check_out={$date}");

        $response->assertOk();
        $response->assertSee('Check-out date must be after check-in date.');
    }

    public function test_stay_over_30_nights_shows_validation_error(): void
    {
        $room = $this->makeRoom();
        $checkIn = Carbon::now('Asia/Makassar')->addDay()->toDateString();
        $checkOut = Carbon::now('Asia/Makassar')->addDays(40)->toDateString();

        $response = $this->get("/room/{$room->slug}?check_in={$checkIn}&check_out={$checkOut}");

        $response->assertOk();
        $response->assertSee('Maximum stay is 30 nights.');
    }

    public function test_detail_page_without_dates_shows_no_availability_label(): void
    {
        $room = $this->makeRoom();

        $response = $this->get("/room/{$room->slug}");

        $response->assertOk();
        $response->assertDontSee('room-detail-availability', false);
    }

    public function test_detail_page_with_valid_dates_shows_availability_label(): void
    {
        $room = $this->makeRoom();
        $checkIn = Carbon::now('Asia/Makassar')->addDay()->toDateString();
        $checkOut = Carbon::now('Asia/Makassar')->addDays(4)->toDateString();

        $response = $this->get("/room/{$room->slug}?check_in={$checkIn}&check_out={$checkOut}");

        $response->assertOk();
        $response->assertSee('3 nights', false);
        $response->assertSee('5 rooms left', false);
    }

    public function test_admin_room_edit_saves_total_units(): void
    {
        $room = $this->makeRoom();
        $admin = $this->admin();

        $response = $this->actingAs($admin)->put("/admin/rooms/{$room->id}", [
            'name' => $room->name,
            'slug' => $room->slug,
            'rate_per_night' => $room->rate_per_night,
            'currency' => $room->currency,
            'size_sqm' => $room->size_sqm,
            'max_guests' => $room->max_guests,
            'bedding' => $room->bedding,
            'total_units' => 12,
            'sort_order' => 0,
        ]);

        $response->assertRedirect(route('admin.rooms.edit', $room));
        $this->assertSame(12, $room->fresh()->total_units);
    }

    public function test_deleting_a_room_with_booking_items_fails_gracefully(): void
    {
        $room = $this->makeRoom();
        $booking = Booking::create(['code' => 'TST-1', 'status' => 'confirmed']);
        BookingItem::create([
            'booking_id' => $booking->id,
            'room_id' => $room->id,
            'check_in' => '2026-10-01',
            'check_out' => '2026-10-04',
            'units' => 1,
        ]);
        $admin = $this->admin();

        $response = $this->actingAs($admin)->delete("/admin/rooms/{$room->id}");

        $response->assertRedirect(route('admin.rooms.index'));
        $response->assertSessionHas('error');
        $this->assertNotNull(Room::find($room->id));
    }
}
