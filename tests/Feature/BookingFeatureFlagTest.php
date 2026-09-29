<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\RatePlan;
use App\Models\Room;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * config('booking.enabled') defaults to false — these tests deliberately do NOT set it,
 * so they exercise the real default behavior rather than assuming an explicit off.
 */
class BookingFeatureFlagTest extends TestCase
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

    public function test_flag_defaults_to_off(): void
    {
        $this->assertFalse(config('booking.enabled'));
    }

    public function test_room_listing_has_no_search_bar_and_ignores_query_params(): void
    {
        $this->makeRoom(['name' => 'Ocean View']);

        $response = $this->get('/room?check_in=not-a-date&check_out=also-not-a-date&guest=3');

        $response->assertOk();
        $response->assertDontSee('room-search-panel-listing', false);
        $response->assertDontSee('Check-in date cannot be in the past');
        $response->assertDontSee('room-detail-availability', false);
        $response->assertSee('Ocean View');
        $response->assertSee('Max 5 guests per room');
    }

    public function test_room_card_links_carry_no_search_params(): void
    {
        $room = $this->makeRoom();

        $response = $this->get('/room?check_in=2026-10-01&check_out=2026-10-04&guest=2');

        $response->assertOk();
        $response->assertSee(htmlspecialchars(route('room.detail', ['room' => $room->slug])), false);
        $response->assertDontSee(htmlspecialchars(route('room.detail', ['room' => $room->slug, 'guest' => 2])), false);
    }

    public function test_room_detail_hides_search_bar_availability_and_booking_summary_but_keeps_rate_plans(): void
    {
        $room = $this->makeRoom();
        RatePlan::create([
            'room_id' => $room->id, 'name' => 'Room Only', 'max_guests' => 2, 'price_per_night' => 500000,
        ]);

        $response = $this->get("/room/{$room->slug}?check_in=2026-10-01&check_out=2026-10-04&guest=2&promo_code=X");

        $response->assertOk();
        $response->assertDontSee('room-search-panel-detail', false);
        $response->assertDontSee('room-detail-availability">', false);
        $response->assertDontSee('This promo code is not valid.');
        $response->assertDontSee('booking-summary"', false);
        $response->assertDontSee('room-rate-plan-book', false);
        // Rate plan row itself is still visible, as price information.
        $response->assertSee('Room Only');
        $response->assertSee('Rp500,000', false);
    }

    public function test_enquiry_cta_links_to_whatsapp_with_room_name(): void
    {
        Setting::updateOrCreate(['key' => 'whatsapp'], ['value' => '081234567890']);
        $room = $this->makeRoom(['name' => 'Garden Suite']);

        $response = $this->get("/room/{$room->slug}");

        $response->assertOk();
        $response->assertSee('https://wa.me/6281234567890', false);
        $response->assertSee(rawurlencode('Hello, I am interested in the Garden Suite.'), false);
    }

    public function test_enquiry_cta_falls_back_to_contact_when_whatsapp_is_empty(): void
    {
        Setting::updateOrCreate(['key' => 'whatsapp'], ['value' => '']);
        $room = $this->makeRoom();

        $response = $this->get("/room/{$room->slug}");

        $response->assertOk();
        $response->assertDontSee('wa.me', false);
        $response->assertSee(route('contact'), false);
    }

    public function test_every_cart_route_returns_404_when_flag_is_off(): void
    {
        $room = $this->makeRoom();
        $plan = RatePlan::create([
            'room_id' => $room->id, 'name' => 'Room Only', 'max_guests' => 2, 'price_per_night' => 500000,
        ]);

        $this->post('/booking/cart/items', ['rate_plan_id' => $plan->id, 'units' => 1])->assertNotFound();
        $this->delete("/booking/cart/items/{$plan->id}")->assertNotFound();
        $this->post('/booking/cart/dates', ['check_in' => '2026-10-01', 'check_out' => '2026-10-04'])->assertNotFound();
        $this->post('/booking/cart/checkout')->assertNotFound();
    }

    public function test_room_listing_no_longer_queries_whatsapp_per_card(): void
    {
        // The footer alone makes several constant, page-level `settings` queries
        // (phone/whatsapp/email/address/maps) unrelated to room cards, so the correct
        // check isn't "how many settings queries total" but "does the count grow with
        // room count" — that's exactly what the old per-card Setting::phoneDigits() call
        // would have caused and what Part 4 removes.
        $this->makeRoom(['is_featured' => false]);
        DB::enableQueryLog();
        $this->get('/room')->assertOk();
        $countWithOneRoom = collect(DB::getQueryLog())->filter(fn ($entry) => str_contains($entry['query'], '"settings"'))->count();
        DB::disableQueryLog();
        DB::flushQueryLog();

        foreach (range(1, 5) as $i) {
            $this->makeRoom(['is_featured' => false]);
        }
        DB::enableQueryLog();
        $this->get('/room')->assertOk();
        $countWithSixRooms = collect(DB::getQueryLog())->filter(fn ($entry) => str_contains($entry['query'], '"settings"'))->count();
        DB::disableQueryLog();

        $this->assertSame(
            $countWithOneRoom,
            $countWithSixRooms,
            'Settings query count must not grow with room count — it should be resolved once per page, not once per card.'
        );
    }
}
