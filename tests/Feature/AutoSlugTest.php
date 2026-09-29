<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Page;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AutoSlugTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    private function admin(): User
    {
        return User::create([
            'name' => 'Admin', 'email' => 'admin-'.uniqid().'@example.com',
            'password' => Hash::make('password'), 'role' => 'admin',
        ]);
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
        ], $overrides));
    }

    private function roomFormData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Test Room',
            'rate_per_night' => 700000,
            'currency' => 'Rp',
            'size_sqm' => 24,
            'max_guests' => 2,
            'bedding' => '1 Double Bed',
            'total_units' => 1,
            'is_active' => '1',
        ], $overrides);
    }

    // ---- Forms have no slug input ----

    public function test_room_create_and_edit_forms_have_no_slug_input(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get('/admin/rooms/create')
            ->assertOk()
            ->assertDontSee('name="slug"', false);

        $room = $this->makeRoom(['name' => 'Existing Room']);

        $this->actingAs($admin)->get("/admin/rooms/{$room->id}/edit")
            ->assertOk()
            ->assertDontSee('name="slug"', false);
    }

    public function test_activity_create_and_edit_forms_have_no_slug_input(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get('/admin/activities/create')
            ->assertOk()
            ->assertDontSee('name="slug"', false);

        $activity = Activity::create(['name' => 'Existing Activity']);

        $this->actingAs($admin)->get("/admin/activities/{$activity->id}/edit")
            ->assertOk()
            ->assertDontSee('name="slug"', false);
    }

    // ---- Slug generation ----

    public function test_creating_a_room_generates_slug_from_name(): void
    {
        $room = $this->makeRoom(['name' => 'Deluxe Ocean View']);

        $this->assertSame('deluxe-ocean-view', $room->slug);
    }

    public function test_creating_an_activity_generates_slug_from_name(): void
    {
        $activity = Activity::create(['name' => 'Sunset Kayaking']);

        $this->assertSame('sunset-kayaking', $activity->slug);
    }

    public function test_duplicate_names_get_unique_slugs(): void
    {
        $first = $this->makeRoom(['name' => 'Superior Room']);
        $second = $this->makeRoom(['name' => 'Superior Room']);

        $this->assertSame('superior-room', $first->slug);
        $this->assertSame('superior-room-2', $second->slug);
    }

    public function test_name_producing_empty_slug_uses_fallback(): void
    {
        $room = $this->makeRoom(['name' => '???']);
        $activity = Activity::create(['name' => '???']);

        $this->assertSame('room', $room->slug);
        $this->assertSame('activity', $activity->slug);
    }

    public function test_editing_the_name_does_not_change_the_slug(): void
    {
        $admin = $this->admin();
        $room = $this->makeRoom(['name' => 'Original Name']);
        $originalSlug = $room->slug;

        $this->actingAs($admin)->put("/admin/rooms/{$room->id}", $this->roomFormData([
            'name' => 'Completely Different Name',
        ]))->assertRedirect(route('admin.rooms.edit', $room));

        $this->assertSame($originalSlug, $room->fresh()->slug);
    }

    // ---- Submitted slug is ignored ----

    public function test_submitted_slug_is_ignored_on_room_create(): void
    {
        $admin = $this->admin();
        $file = \Illuminate\Http\UploadedFile::fake()->image('room.jpg');

        $this->actingAs($admin)->post('/admin/rooms', $this->roomFormData([
            'name' => 'Hacked Room',
            'slug' => 'hacked-slug',
            'image' => $file,
        ]))->assertRedirect();

        $room = Room::where('name', 'Hacked Room')->first();
        $this->assertNotNull($room);
        $this->assertSame('hacked-room', $room->slug);
    }

    public function test_submitted_slug_is_ignored_on_room_update(): void
    {
        $admin = $this->admin();
        $room = $this->makeRoom(['name' => 'Stable Room']);
        $originalSlug = $room->slug;

        $this->actingAs($admin)->put("/admin/rooms/{$room->id}", $this->roomFormData([
            'name' => 'Stable Room',
            'slug' => 'attempted-override',
        ]))->assertRedirect();

        $this->assertSame($originalSlug, $room->fresh()->slug);
    }

    public function test_submitted_slug_is_ignored_on_activity_create_and_update(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/activities', [
            'name' => 'Hacked Activity',
            'slug' => 'hacked-slug',
        ])->assertRedirect();

        $activity = Activity::where('name', 'Hacked Activity')->first();
        $this->assertNotNull($activity);
        $this->assertSame('hacked-activity', $activity->slug);
        $originalSlug = $activity->slug;

        $this->actingAs($admin)->put("/admin/activities/{$activity->id}", [
            'name' => 'Hacked Activity',
            'slug' => 'attempted-override',
        ])->assertRedirect();

        $this->assertSame($originalSlug, $activity->fresh()->slug);
    }

    // ---- Public lookups still work ----

    public function test_room_detail_page_still_works_for_existing_and_newly_created_rooms(): void
    {
        Page::create(['slug' => 'rooms', 'hero_title' => 'Rooms', 'intro_heading' => 'Rooms', 'intro_body' => 'Body.']);
        $existing = $this->makeRoom(['name' => 'Existing Slug Room']);

        $this->get('/room/'.$existing->slug)->assertOk()->assertSee('Existing Slug Room');

        $admin = $this->admin();
        $file = \Illuminate\Http\UploadedFile::fake()->image('new-room.jpg');
        $this->actingAs($admin)->post('/admin/rooms', $this->roomFormData([
            'name' => 'Brand New Room',
            'image' => $file,
        ]))->assertRedirect();

        $newRoom = Room::where('name', 'Brand New Room')->first();
        $this->get('/room/'.$newRoom->slug)->assertOk()->assertSee('Brand New Room');
    }

    // ---- Hotel Information label ----

    public function test_hotel_information_label_and_helper_text_render_on_room_form(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)->get('/admin/rooms/create');

        $response->assertOk();
        $response->assertSee('Hotel Information', false);
        $response->assertDontSee('only rendered on the public Room page for the featured/Family Room composite section');
        $response->assertSee('More Information', false);
    }
}
