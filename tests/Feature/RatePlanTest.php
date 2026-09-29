<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\RatePlan;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RatePlanTest extends TestCase
{
    use RefreshDatabase;

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

    private function admin(): User
    {
        return User::create([
            'name' => 'Admin',
            'email' => 'admin-'.uniqid().'@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);
    }

    public function test_admin_can_create_rate_plan(): void
    {
        $room = $this->makeRoom();
        $admin = $this->admin();

        $response = $this->actingAs($admin)->post("/admin/rooms/{$room->id}/rate-plans", [
            'name' => 'Room Only',
            'max_guests' => 2,
            'price_per_night' => 500000,
        ]);

        $response->assertRedirect(route('admin.rooms.edit', $room));
        $this->assertDatabaseHas('rate_plans', ['room_id' => $room->id, 'name' => 'Room Only', 'price_per_night' => 500000]);
    }

    public function test_admin_can_update_and_delete_rate_plan(): void
    {
        $room = $this->makeRoom();
        $admin = $this->admin();
        $plan = RatePlan::create([
            'room_id' => $room->id, 'name' => 'Room Only', 'max_guests' => 2, 'price_per_night' => 500000,
        ]);

        $this->actingAs($admin)->patch("/admin/rooms/{$room->id}/rate-plans/{$plan->id}", [
            'name' => 'Room Only Updated',
            'max_guests' => 2,
            'price_per_night' => 550000,
        ])->assertRedirect(route('admin.rooms.edit', $room));

        $this->assertSame('Room Only Updated', $plan->fresh()->name);

        $this->actingAs($admin)->delete("/admin/rooms/{$room->id}/rate-plans/{$plan->id}")
            ->assertRedirect(route('admin.rooms.edit', $room));

        $this->assertDatabaseMissing('rate_plans', ['id' => $plan->id]);
    }

    public function test_compare_at_price_must_be_greater_than_price(): void
    {
        $room = $this->makeRoom();
        $admin = $this->admin();

        $this->actingAs($admin)->post("/admin/rooms/{$room->id}/rate-plans", [
            'name' => 'Bad Plan',
            'max_guests' => 2,
            'price_per_night' => 700000,
            'compare_at_price_per_night' => 600000,
        ])->assertSessionHasErrors('compare_at_price_per_night');

        $this->assertDatabaseMissing('rate_plans', ['name' => 'Bad Plan']);
    }

    public function test_max_guests_cannot_exceed_room_max_guests(): void
    {
        $room = $this->makeRoom(['max_guests' => 5]);
        $admin = $this->admin();

        $this->actingAs($admin)->post("/admin/rooms/{$room->id}/rate-plans", [
            'name' => 'Too Many',
            'max_guests' => 10,
            'price_per_night' => 500000,
        ])->assertSessionHasErrors('max_guests');

        $this->assertDatabaseMissing('rate_plans', ['name' => 'Too Many']);
    }

    public function test_inactive_plans_are_hidden_publicly(): void
    {
        $room = $this->makeRoom();
        RatePlan::create([
            'room_id' => $room->id, 'name' => 'Active Plan', 'max_guests' => 2,
            'price_per_night' => 500000, 'is_active' => true,
        ]);
        RatePlan::create([
            'room_id' => $room->id, 'name' => 'Inactive Plan', 'max_guests' => 2,
            'price_per_night' => 400000, 'is_active' => false,
        ]);

        $response = $this->get("/room/{$room->slug}");

        $response->assertOk();
        $response->assertSee('Active Plan');
        $response->assertDontSee('Inactive Plan');
    }

    public function test_row_renders_with_and_without_compare_at_and_description(): void
    {
        $room = $this->makeRoom();
        RatePlan::create([
            'room_id' => $room->id, 'name' => 'Plain Plan', 'max_guests' => 2,
            'price_per_night' => 500000,
        ]);
        RatePlan::create([
            'room_id' => $room->id, 'name' => 'Fancy Plan', 'max_guests' => 2,
            'price_per_night' => 500000, 'compare_at_price_per_night' => 600000,
            'description' => 'Includes late checkout.',
        ]);

        $response = $this->get("/room/{$room->slug}");

        $response->assertOk();
        $response->assertSee('Plain Plan');
        $response->assertSee('Fancy Plan');
        $response->assertSee('Includes late checkout.');
        $response->assertSee('room-rate-plan-price-compare', false);
    }

    public function test_start_from_uses_lowest_active_plan_price(): void
    {
        $room = $this->makeRoom(['rate_per_night' => 900000]);
        RatePlan::create(['room_id' => $room->id, 'name' => 'Expensive', 'max_guests' => 2, 'price_per_night' => 800000]);
        RatePlan::create(['room_id' => $room->id, 'name' => 'Cheap', 'max_guests' => 2, 'price_per_night' => 500000]);
        RatePlan::create(['room_id' => $room->id, 'name' => 'Inactive Cheapest', 'max_guests' => 2, 'price_per_night' => 100000, 'is_active' => false]);

        $response = $this->get("/room/{$room->slug}");

        $response->assertOk();
        $response->assertSee('Rp500,000', false);
        $response->assertDontSee('Rp100,000', false);
    }

    public function test_start_from_falls_back_to_rate_per_night_when_no_active_plans(): void
    {
        $room = $this->makeRoom(['rate_per_night' => 900000]);

        $response = $this->get("/room/{$room->slug}");

        $response->assertOk();
        $response->assertSee('Rp900,000', false);
    }

    public function test_room_listing_has_no_rate_plan_n_plus_one(): void
    {
        Page::create(['slug' => 'rooms', 'hero_title' => 'Rooms', 'intro_heading' => 'Rooms', 'intro_body' => 'Body.']);

        foreach (range(1, 5) as $i) {
            $room = $this->makeRoom(['is_featured' => false]);
            RatePlan::create(['room_id' => $room->id, 'name' => 'Plan', 'max_guests' => 2, 'price_per_night' => 500000]);
        }

        DB::enableQueryLog();
        $response = $this->get('/room');
        $log = DB::getQueryLog();
        DB::disableQueryLog();

        $response->assertOk();

        // Isolate rate_plans queries specifically from unrelated per-render query count
        // (e.g. room-card.blade.php's pre-existing per-card Setting::phoneDigits('whatsapp')
        // lookup, which already scales with room count and is out of this task's scope).
        // Eager loading means exactly one rate_plans query for all 5 rooms, not one each.
        $ratePlanQueries = collect($log)->filter(fn ($entry) => str_contains($entry['query'], 'rate_plans'));

        $this->assertCount(1, $ratePlanQueries, 'Expected exactly one rate_plans query for the whole listing, not one per room.');
    }
}
