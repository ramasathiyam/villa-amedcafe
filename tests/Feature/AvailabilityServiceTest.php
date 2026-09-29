<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Room;
use App\Services\AvailabilityService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AvailabilityServiceTest extends TestCase
{
    use RefreshDatabase;

    private AvailabilityService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(AvailabilityService::class);
    }

    private function makeRoom(int $totalUnits = 5): Room
    {
        return Room::create([
            'slug' => 'test-room-'.uniqid(),
            'name' => 'Test Room',
            'rate_per_night' => 700000,
            'currency' => 'Rp',
            'size_sqm' => 24,
            'max_guests' => 2,
            'bedding' => '1 Double Bed',
            'image' => '/images/rooms/room-card.png',
            'is_active' => true,
            'total_units' => $totalUnits,
        ]);
    }

    private function makeBooking(string $status, ?Carbon $expiresAt = null): Booking
    {
        return Booking::create([
            'code' => 'TST-'.uniqid(),
            'status' => $status,
            'expires_at' => $expiresAt,
        ]);
    }

    private function makeItem(Booking $booking, Room $room, string $checkIn, string $checkOut, int $units = 1): BookingItem
    {
        return BookingItem::create([
            'booking_id' => $booking->id,
            'room_id' => $room->id,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'units' => $units,
        ]);
    }

    public function test_nights_counts_correctly(): void
    {
        $this->assertSame(3, $this->service->nights(Carbon::parse('2026-10-01'), Carbon::parse('2026-10-04')));
    }

    public function test_no_bookings_available_equals_total_units(): void
    {
        $room = $this->makeRoom(5);

        $this->assertSame(5, $this->service->availableUnits($room, Carbon::parse('2026-10-01'), Carbon::parse('2026-10-04')));
    }

    public function test_confirmed_booking_reduces_availability(): void
    {
        $room = $this->makeRoom(5);
        $booking = $this->makeBooking('confirmed');
        $this->makeItem($booking, $room, '2026-10-01', '2026-10-04');

        $this->assertSame(4, $this->service->availableUnits($room, Carbon::parse('2026-10-01'), Carbon::parse('2026-10-04')));
    }

    public function test_cancelled_and_expired_bookings_do_not_reduce_availability(): void
    {
        $room = $this->makeRoom(5);

        $cancelled = $this->makeBooking('cancelled');
        $this->makeItem($cancelled, $room, '2026-10-01', '2026-10-04');

        $expired = $this->makeBooking('expired');
        $this->makeItem($expired, $room, '2026-10-01', '2026-10-04');

        $this->assertSame(5, $this->service->availableUnits($room, Carbon::parse('2026-10-01'), Carbon::parse('2026-10-04')));
    }

    public function test_pending_with_future_expiry_reduces_availability(): void
    {
        $room = $this->makeRoom(5);
        $booking = $this->makeBooking('pending', now()->addMinutes(30));
        $this->makeItem($booking, $room, '2026-10-01', '2026-10-04');

        $this->assertSame(4, $this->service->availableUnits($room, Carbon::parse('2026-10-01'), Carbon::parse('2026-10-04')));
    }

    public function test_pending_with_past_expiry_does_not_reduce_availability(): void
    {
        $room = $this->makeRoom(5);
        $booking = $this->makeBooking('pending', now()->subMinutes(30));
        $this->makeItem($booking, $room, '2026-10-01', '2026-10-04');

        $this->assertSame(5, $this->service->availableUnits($room, Carbon::parse('2026-10-01'), Carbon::parse('2026-10-04')));
    }

    public function test_back_to_back_stays_do_not_conflict(): void
    {
        $room = $this->makeRoom(1);
        $booking = $this->makeBooking('confirmed');
        // Occupies nights Oct 1–2; checking out Oct 3 frees the room for a same-day check-in.
        $this->makeItem($booking, $room, '2026-10-01', '2026-10-03');

        $this->assertSame(1, $this->service->availableUnits($room, Carbon::parse('2026-10-03'), Carbon::parse('2026-10-05')));
    }

    public function test_max_concurrent_not_sum_for_non_overlapping_bookings(): void
    {
        $room = $this->makeRoom(5);
        $bookingA = $this->makeBooking('confirmed');
        $this->makeItem($bookingA, $room, '2026-10-01', '2026-10-03', 1);
        $bookingB = $this->makeBooking('confirmed');
        $this->makeItem($bookingB, $room, '2026-10-03', '2026-10-05', 1);

        // A 1–5 Oct search must see max concurrent (1), not the sum (2) → 5 - 1 = 4.
        $this->assertSame(4, $this->service->availableUnits($room, Carbon::parse('2026-10-01'), Carbon::parse('2026-10-05')));
    }

    public function test_fully_booked_returns_zero_never_negative(): void
    {
        $room = $this->makeRoom(2);
        $booking = $this->makeBooking('confirmed');
        $this->makeItem($booking, $room, '2026-10-01', '2026-10-04', 5);

        $this->assertSame(0, $this->service->availableUnits($room, Carbon::parse('2026-10-01'), Carbon::parse('2026-10-04')));
    }
}
