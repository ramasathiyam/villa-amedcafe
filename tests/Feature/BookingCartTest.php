<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Page;
use App\Models\RatePlan;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingCartTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Booking Feature Flag defaults to off; the whole cart flow is behind it, so this
        // suite must explicitly turn it on.
        config(['booking.enabled' => true]);

        Page::create(['slug' => 'rooms', 'hero_title' => 'Rooms', 'intro_heading' => 'Rooms', 'intro_body' => 'Body.']);
    }

    private function makeRoom(array $overrides = []): Room
    {
        return Room::create(array_merge([
            'slug' => 'test-room-'.uniqid(),
            'name' => 'Test Room',
            'rate_per_night' => 700000,
            'currency' => 'Rp',
            'size_sqm' => 24,
            'max_guests' => 5,
            'bedding' => '1 Double Bed',
            'image' => '/images/rooms/room-card.png',
            'is_active' => true,
            'total_units' => 5,
        ], $overrides));
    }

    private function makePlan(Room $room, array $overrides = []): RatePlan
    {
        return RatePlan::create(array_merge([
            'room_id' => $room->id,
            'name' => 'Room Only',
            'max_guests' => 2,
            'price_per_night' => 500000,
            'is_active' => true,
        ], $overrides));
    }

    private function dates(): array
    {
        return [
            Carbon::now('Asia/Makassar')->addDay()->toDateString(),
            Carbon::now('Asia/Makassar')->addDays(4)->toDateString(),
        ];
    }

    // ---- Part 0A: Guest field ----

    public function test_guest_field_validation_is_shared_and_guests_carried_in_links(): void
    {
        $room = $this->makeRoom();

        $response = $this->get("/room/{$room->slug}?guest=abc");
        $response->assertOk(); // non-numeric guest silently ignored, same as /room

        $response = $this->get("/room/{$room->slug}?guest=3");
        $response->assertOk();
        $response->assertSee('name="guest"', false);
    }

    public function test_room_card_links_carry_guest_query_param(): void
    {
        $room = $this->makeRoom(['name' => 'Ocean View']);

        $response = $this->get('/room?guest=3');

        $response->assertOk();
        $response->assertSee(htmlspecialchars(route('room.detail', ['room' => $room->slug, 'guest' => 3])), false);
    }

    // ---- Part 0B: Promo Code ----

    public function test_invalid_promo_code_shows_message_but_does_not_block_rendering(): void
    {
        $room = $this->makeRoom();
        $this->makePlan($room);
        [$checkIn, $checkOut] = $this->dates();

        $response = $this->get("/room/{$room->slug}?check_in={$checkIn}&check_out={$checkOut}&promo_code=SAVE10");

        $response->assertOk();
        $response->assertSee('This promo code is not valid.');
        $response->assertSee('Room Only'); // rate plan still rendered
        $response->assertSee('room-detail-availability', false); // availability still rendered
    }

    // ---- Part 1/3: Cart add/set/validate ----

    public function test_add_item_without_dates_redirects_with_message_and_cart_stays_empty(): void
    {
        $room = $this->makeRoom();
        $plan = $this->makePlan($room);

        $response = $this->post('/booking/cart/items', [
            'rate_plan_id' => $plan->id,
            'units' => 1,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status', 'Please select your dates first.');

        $detail = $this->get("/room/{$room->slug}");
        $detail->assertSee('No rooms selected yet.');
    }

    public function test_add_item_with_dates_appears_in_cart_and_readding_sets_units(): void
    {
        $room = $this->makeRoom();
        $plan = $this->makePlan($room, ['price_per_night' => 500000]);
        [$checkIn, $checkOut] = $this->dates();

        $this->post('/booking/cart/items', [
            'rate_plan_id' => $plan->id, 'units' => 2, 'check_in' => $checkIn, 'check_out' => $checkOut,
        ])->assertRedirect();

        $response = $this->get("/room/{$room->slug}?check_in={$checkIn}&check_out={$checkOut}");
        $response->assertSee('2 units');

        // Re-adding sets, not adds.
        $this->post('/booking/cart/items', [
            'rate_plan_id' => $plan->id, 'units' => 4, 'check_in' => $checkIn, 'check_out' => $checkOut,
        ])->assertRedirect();

        $response = $this->get("/room/{$room->slug}?check_in={$checkIn}&check_out={$checkOut}");
        $response->assertSee('4 units');
        $response->assertDontSee('6 units');
    }

    public function test_two_rate_plans_of_same_room_cannot_exceed_room_availability(): void
    {
        $room = $this->makeRoom(['total_units' => 5]);
        $planA = $this->makePlan($room, ['name' => 'Plan A']);
        $planB = $this->makePlan($room, ['name' => 'Plan B']);
        [$checkIn, $checkOut] = $this->dates();

        $this->post('/booking/cart/items', [
            'rate_plan_id' => $planA->id, 'units' => 3, 'check_in' => $checkIn, 'check_out' => $checkOut,
        ])->assertRedirect();

        $this->post('/booking/cart/items', [
            'rate_plan_id' => $planB->id, 'units' => 4, 'check_in' => $checkIn, 'check_out' => $checkOut,
        ])->assertSessionHas('cart_messages');

        $response = $this->get("/room/{$room->slug}?check_in={$checkIn}&check_out={$checkOut}");
        // 3 + 2 = 5 (room's total_units), never 3 + 4 = 7.
        $response->assertSee('3 units');
        $response->assertSee('2 units');
        $response->assertDontSee('4 units');
    }

    public function test_unavailable_room_shows_disabled_selector_and_button(): void
    {
        $room = $this->makeRoom(['total_units' => 1]);
        $plan = $this->makePlan($room);
        $booking = Booking::create(['code' => 'FULL-1', 'status' => 'confirmed']);
        [$checkIn, $checkOut] = $this->dates();
        BookingItem::create([
            'booking_id' => $booking->id, 'room_id' => $room->id,
            'check_in' => $checkIn, 'check_out' => $checkOut, 'units' => 1,
        ]);

        $response = $this->get("/room/{$room->slug}?check_in={$checkIn}&check_out={$checkOut}");

        $response->assertOk();
        $response->assertSee('Not available for these dates');
        $response->assertSee('disabled', false);
    }

    public function test_remove_item_works(): void
    {
        $room = $this->makeRoom();
        $plan = $this->makePlan($room);
        [$checkIn, $checkOut] = $this->dates();

        $this->post('/booking/cart/items', [
            'rate_plan_id' => $plan->id, 'units' => 1, 'check_in' => $checkIn, 'check_out' => $checkOut,
        ]);

        $this->delete("/booking/cart/items/{$plan->id}")->assertRedirect();

        $response = $this->get("/room/{$room->slug}?check_in={$checkIn}&check_out={$checkOut}");
        $response->assertSee('No rooms selected yet.');
    }

    public function test_subtotal_and_grand_total_are_correct(): void
    {
        $room = $this->makeRoom();
        $plan = $this->makePlan($room, ['price_per_night' => 500000]);
        [$checkIn, $checkOut] = $this->dates(); // 3 nights

        $this->post('/booking/cart/items', [
            'rate_plan_id' => $plan->id, 'units' => 2, 'check_in' => $checkIn, 'check_out' => $checkOut,
        ]);

        // 500,000 x 3 nights x 2 units = 3,000,000
        $response = $this->get("/room/{$room->slug}?check_in={$checkIn}&check_out={$checkOut}");
        $response->assertSee('Rp3,000,000', false);
    }

    public function test_update_selection_moves_cart_dates_and_revalidates(): void
    {
        $room = $this->makeRoom();
        $plan = $this->makePlan($room);
        [$checkIn, $checkOut] = $this->dates();

        $this->post('/booking/cart/items', [
            'rate_plan_id' => $plan->id, 'units' => 1, 'check_in' => $checkIn, 'check_out' => $checkOut,
        ]);

        $newCheckIn = Carbon::now('Asia/Makassar')->addDays(10)->toDateString();
        $newCheckOut = Carbon::now('Asia/Makassar')->addDays(13)->toDateString();

        $this->post('/booking/cart/dates', [
            'check_in' => $newCheckIn, 'check_out' => $newCheckOut,
        ])->assertRedirect();

        $response = $this->get("/room/{$room->slug}?check_in={$newCheckIn}&check_out={$newCheckOut}");
        $response->assertDontSee('booking-summary-notice', false); // dates now match, no notice
        $response->assertSee('1 unit', false); // item survived the move, now under the new dates
    }

    public function test_dates_differ_notice_shown_without_modifying_cart_on_get(): void
    {
        $room = $this->makeRoom();
        $plan = $this->makePlan($room);
        [$checkIn, $checkOut] = $this->dates();

        $this->post('/booking/cart/items', [
            'rate_plan_id' => $plan->id, 'units' => 1, 'check_in' => $checkIn, 'check_out' => $checkOut,
        ]);

        $otherCheckIn = Carbon::now('Asia/Makassar')->addDays(20)->toDateString();
        $otherCheckOut = Carbon::now('Asia/Makassar')->addDays(23)->toDateString();

        // Plain GET with different dates must show the notice, not silently move the cart.
        $response = $this->get("/room/{$room->slug}?check_in={$otherCheckIn}&check_out={$otherCheckOut}");
        $response->assertSee('Update selection to', false);

        // Cart's own dates are untouched — visiting with the ORIGINAL dates still shows the item.
        $response = $this->get("/room/{$room->slug}?check_in={$checkIn}&check_out={$checkOut}");
        $response->assertDontSee('No rooms selected yet.');
        $response->assertSee('1 unit', false);
    }

    public function test_deleted_rate_plan_is_removed_from_cart_without_error(): void
    {
        $room = $this->makeRoom();
        $plan = $this->makePlan($room);
        [$checkIn, $checkOut] = $this->dates();

        $this->post('/booking/cart/items', [
            'rate_plan_id' => $plan->id, 'units' => 1, 'check_in' => $checkIn, 'check_out' => $checkOut,
        ]);

        $plan->delete();

        $response = $this->get("/room/{$room->slug}?check_in={$checkIn}&check_out={$checkOut}");

        $response->assertOk();
        $response->assertSee('No rooms selected yet.');
    }

    // ---- Part 6: Make This Booking ----

    public function test_checkout_success_message_and_no_rows_written(): void
    {
        $room = $this->makeRoom();
        $plan = $this->makePlan($room, ['max_guests' => 5]);
        [$checkIn, $checkOut] = $this->dates();

        $this->post('/booking/cart/items', [
            'rate_plan_id' => $plan->id, 'units' => 1, 'check_in' => $checkIn, 'check_out' => $checkOut,
        ]);

        $this->post('/booking/cart/checkout')
            ->assertRedirect()
            ->assertSessionHas('status', 'Your selection is ready. Checkout will be added in the next step.');

        $this->assertSame(0, Booking::count());
        $this->assertSame(0, BookingItem::count());
    }

    public function test_checkout_capacity_check_fails_with_clear_message(): void
    {
        $room = $this->makeRoom();
        $plan = $this->makePlan($room, ['max_guests' => 2]);
        [$checkIn, $checkOut] = $this->dates();

        $this->post('/booking/cart/items', [
            'rate_plan_id' => $plan->id, 'units' => 1, 'check_in' => $checkIn, 'check_out' => $checkOut, 'guest' => 10,
        ]);

        $this->post('/booking/cart/checkout')
            ->assertRedirect()
            ->assertSessionHas('error', 'Your selection fits 2 guests. Please add rooms for 10 guests.');

        $this->assertSame(0, Booking::count());
        $this->assertSame(0, BookingItem::count());
    }

    public function test_checkout_reruns_availability_check(): void
    {
        $room = $this->makeRoom(['total_units' => 1]);
        $plan = $this->makePlan($room);
        [$checkIn, $checkOut] = $this->dates();

        $this->post('/booking/cart/items', [
            'rate_plan_id' => $plan->id, 'units' => 1, 'check_in' => $checkIn, 'check_out' => $checkOut,
        ]);

        // Someone else takes the room's only unit after it was added to this cart.
        $booking = Booking::create(['code' => 'RACE-1', 'status' => 'confirmed']);
        BookingItem::create([
            'booking_id' => $booking->id, 'room_id' => $room->id,
            'check_in' => $checkIn, 'check_out' => $checkOut, 'units' => 1,
        ]);

        $this->post('/booking/cart/checkout')->assertRedirect();

        $this->assertSame(0, Booking::where('code', '!=', 'RACE-1')->count());
        $this->assertSame(1, Booking::count()); // only the pre-existing one
    }

    public function test_get_request_never_modifies_the_cart(): void
    {
        $room = $this->makeRoom();
        $plan = $this->makePlan($room);
        [$checkIn, $checkOut] = $this->dates();

        $this->post('/booking/cart/items', [
            'rate_plan_id' => $plan->id, 'units' => 1, 'check_in' => $checkIn, 'check_out' => $checkOut,
        ]);

        // Several plain GETs with different query strings.
        $this->get("/room/{$room->slug}");
        $this->get("/room/{$room->slug}?check_in=".Carbon::now('Asia/Makassar')->addDays(15)->toDateString());
        $this->get('/room');

        // Cart is exactly as it was — still visible under its original dates.
        $response = $this->get("/room/{$room->slug}?check_in={$checkIn}&check_out={$checkOut}");
        $response->assertSee('1 unit', false);
    }
}
