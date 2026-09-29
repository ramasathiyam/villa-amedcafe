<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\Event;
use App\Models\Page;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EventTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Page::create(['slug' => 'home', 'hero_title' => 'Home', 'intro_heading' => 'Home', 'intro_body' => 'Body.']);
        Banner::create([
            'page' => 'home', 'eyebrow' => 'Explore', 'heading' => 'Amed Escape', 'body' => 'Body.',
            'background_image' => '/images/home/promo-amed-escape.png', 'is_active' => true,
        ]);
        Storage::fake('public');
    }

    private function admin(): User
    {
        return User::create([
            'name' => 'Admin', 'email' => 'admin-'.uniqid().'@example.com',
            'password' => Hash::make('password'), 'role' => 'admin',
        ]);
    }

    private function makeEvent(array $overrides = []): Event
    {
        return Event::create(array_merge([
            'label' => 'Seasonal Event',
            'title' => 'Test Event',
            'description' => 'Test description.',
            'background_image' => '/images/home/promo-halloween.png',
            'is_active' => true,
            'sort_order' => 0,
        ], $overrides));
    }

    public function test_admin_can_create_event_with_image(): void
    {
        $admin = $this->admin();
        $file = UploadedFile::fake()->image('event.jpg');

        $response = $this->actingAs($admin)->post('/admin/events', [
            'label' => 'Seasonal Event',
            'title' => 'Christmas',
            'description' => 'Christmas description.',
            'background_image' => $file,
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.events.index'));
        $this->assertDatabaseHas('events', ['title' => 'Christmas']);
        Storage::disk('public')->assertExists(Event::where('title', 'Christmas')->first()->background_image);
    }

    public function test_title_and_background_image_required_on_create(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/events', [
            'label' => 'Seasonal Event',
            'description' => 'Description.',
        ])->assertSessionHasErrors(['title', 'background_image']);

        $this->assertSame(0, Event::count());
    }

    public function test_display_until_must_not_be_before_display_from(): void
    {
        $admin = $this->admin();
        $file = UploadedFile::fake()->image('event.jpg');

        $this->actingAs($admin)->post('/admin/events', [
            'label' => 'Seasonal Event',
            'title' => 'Bad Dates',
            'description' => 'Description.',
            'background_image' => $file,
            'display_from' => '2026-12-31',
            'display_until' => '2026-12-01',
        ])->assertSessionHasErrors('display_until');

        $this->assertDatabaseMissing('events', ['title' => 'Bad Dates']);
    }

    public function test_link_url_accepts_full_url_or_internal_path(): void
    {
        $admin = $this->admin();
        $event = $this->makeEvent();

        $this->actingAs($admin)->put("/admin/events/{$event->id}", [
            'label' => $event->label, 'title' => $event->title, 'description' => $event->description,
            'link_url' => '/room',
        ])->assertSessionDoesntHaveErrors('link_url');

        $this->actingAs($admin)->put("/admin/events/{$event->id}", [
            'label' => $event->label, 'title' => $event->title, 'description' => $event->description,
            'link_url' => 'https://example.com',
        ])->assertSessionDoesntHaveErrors('link_url');

        $this->actingAs($admin)->put("/admin/events/{$event->id}", [
            'label' => $event->label, 'title' => $event->title, 'description' => $event->description,
            'link_url' => 'not a url',
        ])->assertSessionHasErrors('link_url');
    }

    public function test_admin_can_update_and_delete_event(): void
    {
        $admin = $this->admin();
        $event = $this->makeEvent();

        $this->actingAs($admin)->put("/admin/events/{$event->id}", [
            'label' => 'Updated Label', 'title' => 'Updated Title', 'description' => 'Updated.',
        ])->assertRedirect(route('admin.events.index'));

        $this->assertSame('Updated Title', $event->fresh()->title);

        $this->actingAs($admin)->delete("/admin/events/{$event->id}")
            ->assertRedirect(route('admin.events.index'));

        $this->assertDatabaseMissing('events', ['id' => $event->id]);
    }

    public function test_deleting_event_removes_its_image_file(): void
    {
        $admin = $this->admin();
        $file = UploadedFile::fake()->image('to-delete.jpg');
        $this->actingAs($admin)->post('/admin/events', [
            'label' => 'L', 'title' => 'To Delete', 'description' => 'D', 'background_image' => $file,
        ]);
        $event = Event::where('title', 'To Delete')->first();
        $imagePath = $event->background_image;
        Storage::disk('public')->assertExists($imagePath);

        $this->actingAs($admin)->delete("/admin/events/{$event->id}");

        Storage::disk('public')->assertMissing($imagePath);
    }

    // ---- Visibility window ----

    public function test_visibility_window_before_within_after_and_nulls(): void
    {
        $today = Carbon::now('Asia/Makassar');

        $before = $this->makeEvent(['title' => 'Before', 'display_from' => $today->copy()->addDays(5)]);
        $within = $this->makeEvent(['title' => 'Within', 'display_from' => $today->copy()->subDays(2), 'display_until' => $today->copy()->addDays(2)]);
        $after = $this->makeEvent(['title' => 'After', 'display_until' => $today->copy()->subDays(1)]);
        $unlimited = $this->makeEvent(['title' => 'Unlimited', 'display_from' => null, 'display_until' => null]);

        $visible = Event::visibleOnHome()->pluck('title')->all();

        $this->assertNotContains('Before', $visible);
        $this->assertContains('Within', $visible);
        $this->assertNotContains('After', $visible);
        $this->assertContains('Unlimited', $visible);
    }

    public function test_inactive_event_never_visible_regardless_of_dates(): void
    {
        $this->makeEvent(['title' => 'Inactive', 'is_active' => false]);

        $this->assertNotContains('Inactive', Event::visibleOnHome()->pluck('title')->all());
    }

    // ---- Ordering ----

    public function test_ordering_rule(): void
    {
        $today = Carbon::now('Asia/Makassar');

        // sort_order 1, no display_until -> should come after sort_order 0 items.
        $c = $this->makeEvent(['title' => 'C', 'sort_order' => 1, 'display_until' => null]);
        // sort_order 0, ends later.
        $a = $this->makeEvent(['title' => 'A', 'sort_order' => 0, 'display_until' => $today->copy()->addDays(10)]);
        // sort_order 0, ends sooner -> should come before A.
        $b = $this->makeEvent(['title' => 'B', 'sort_order' => 0, 'display_until' => $today->copy()->addDays(5)]);
        // sort_order 0, no display_until -> after B and A (both have a display_until).
        $d = $this->makeEvent(['title' => 'D', 'sort_order' => 0, 'display_until' => null]);

        $order = Event::visibleOnHome()->pluck('title')->all();

        $this->assertSame(['B', 'A', 'D', 'C'], $order);
    }

    // ---- Admin Status ----

    public function test_admin_status_values(): void
    {
        $today = Carbon::now('Asia/Makassar');

        $active = $this->makeEvent(['title' => 'S-Active']);
        $scheduled = $this->makeEvent(['title' => 'S-Scheduled', 'display_from' => $today->copy()->addDays(5)]);
        $ended = $this->makeEvent(['title' => 'S-Ended', 'display_until' => $today->copy()->subDays(1)]);
        $inactive = $this->makeEvent(['title' => 'S-Inactive', 'is_active' => false]);

        $this->assertSame('Active', $active->status());
        $this->assertSame('Scheduled', $scheduled->status());
        $this->assertSame('Ended', $ended->status());
        $this->assertSame('Inactive', $inactive->status());
    }

    // ---- Dashboard ----

    public function test_dashboard_events_card_counts_only_visible_events(): void
    {
        $admin = $this->admin();
        $today = Carbon::now('Asia/Makassar');

        $this->makeEvent(['title' => 'Visible']);
        $this->makeEvent(['title' => 'Not Visible', 'display_from' => $today->copy()->addDays(5)]);

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertOk();
        $response->assertSeeInOrder(['Events</p>', '1']);
    }

    // ---- Sidebar ----

    public function test_sidebar_shows_events_active_on_events_pages(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)->get('/admin/events');
        $response->assertOk();
        preg_match('/<a[^>]*class="admin-nav-link[^"]*"[^>]*>Events<\/a>/', $response->getContent(), $eventsLink);
        $this->assertNotEmpty($eventsLink);
        $this->assertStringContainsString('is-active', $eventsLink[0]);
    }

    // ---- Public rendering ----

    public function test_events_render_only_on_home(): void
    {
        $this->makeEvent(['title' => 'Home Only Event']);

        $this->get('/')->assertSee('Home Only Event');
        // The Home Event should not leak onto Room via the sitewide Promo Banner
        // component (a separate feature entirely).
        $this->get('/room')->assertDontSee('Home Only Event');
    }

    public function test_no_visible_events_means_no_event_markup_on_home(): void
    {
        $this->makeEvent(['title' => 'Hidden', 'is_active' => false]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertDontSee('Hidden');
    }
}
