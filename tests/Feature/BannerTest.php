<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\DiningVenue;
use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BannerTest extends TestCase
{
    use RefreshDatabase;

    private const PAGES = [
        'home' => 'Home',
        'rooms' => 'Rooms',
        'activities' => 'Activity',
        'spa' => 'Spa',
        'contact' => 'Contact',
        'resto-amed-cafe' => 'Resto Amed Café',
        'barak-rooftop-and-bar' => 'Barak Rooftop & Bar',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['home', 'rooms', 'activities', 'spa', 'resto-amed-cafe', 'barak-rooftop-and-bar'] as $slug) {
            Page::create(['slug' => $slug, 'hero_title' => 'T', 'intro_heading' => 'T', 'intro_body' => 'Body.']);
        }

        foreach (['resto-amed-cafe' => 'Resto Amed Cafe', 'barak-rooftop-and-bar' => 'Barak Rooftop and Bar'] as $slug => $name) {
            DiningVenue::create([
                'slug' => $slug, 'name' => $name, 'is_active' => true,
                'hero_title' => $name, 'hero_image' => '/images/dining/placeholder.png',
                'intro_heading' => $name, 'intro_body' => 'Body.',
            ]);
        }

        foreach (self::PAGES as $page => $label) {
            Banner::create([
                'page' => $page,
                'eyebrow' => 'Explore',
                'heading' => 'Amed Escape',
                'body' => 'Peaceful charm of Amed.',
                'background_image' => '/images/home/promo-amed-escape.png',
                'link_url' => null,
                'is_active' => true,
            ]);
        }

        Storage::fake('public');
    }

    private function admin(): User
    {
        return User::create([
            'name' => 'Admin', 'email' => 'admin-'.uniqid().'@example.com',
            'password' => Hash::make('password'), 'role' => 'admin',
        ]);
    }

    // ---- Rename ----

    public function test_old_promo_banner_routes_no_longer_exist(): void
    {
        $this->get('/admin/promo-banner')->assertNotFound();
    }

    public function test_admin_menu_shows_banner_in_specified_position_with_correct_active_state(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)->get('/admin/banners');

        $response->assertOk();
        preg_match('/<a[^>]*class="admin-nav-link[^"]*"[^>]*>Banner<\/a>/', $response->getContent(), $link);
        $this->assertNotEmpty($link, 'Banner sidebar link not found.');
        $this->assertStringContainsString('is-active', $link[0]);

        // Same position: between Site Pages and Site Settings.
        $content = $response->getContent();
        $sitePagesPos = strpos($content, '>Site Pages<');
        $bannerPos = strpos($content, '>Banner<');
        $settingsPos = strpos($content, '>Site Settings<');
        $this->assertTrue($sitePagesPos < $bannerPos && $bannerPos < $settingsPos);
    }

    public function test_banner_active_on_edit_page_too(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)->get('/admin/banners/home/edit');

        $response->assertOk();
        preg_match('/<a[^>]*class="admin-nav-link[^"]*"[^>]*>Banner<\/a>/', $response->getContent(), $link);
        $this->assertNotEmpty($link);
        $this->assertStringContainsString('is-active', $link[0]);
    }

    // ---- Admin index ----

    public function test_banner_index_lists_exactly_seven_pages_in_navbar_order(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)->get('/admin/banners');

        $response->assertOk();
        preg_match_all('/admin-table-name">([^<]*)</', $response->getContent(), $matches);
        $this->assertSame(array_values(self::PAGES), array_map('html_entity_decode', $matches[1]));
    }

    public function test_no_create_or_delete_routes_exist_for_banners(): void
    {
        $admin = $this->admin();

        // "create" isn't a registered path segment here (only index/edit/update exist),
        // so GET matches the {banner} wildcard of the PUT/PATCH update route instead —
        // Laravel correctly reports 405 (path matched, method didn't), not 404.
        $this->actingAs($admin)->get('/admin/banners/create')->assertMethodNotAllowed();
        $this->actingAs($admin)->delete('/admin/banners/home')->assertMethodNotAllowed();
    }

    // ---- Editing one page only affects that page ----

    public function test_editing_one_pages_banner_changes_only_that_page(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->put('/admin/banners/rooms', [
            'eyebrow' => 'Explore', 'heading' => 'Rooms Only Heading', 'body' => 'Body.', 'is_active' => '1',
        ])->assertRedirect(route('admin.banners.edit', 'rooms'));

        $this->assertDatabaseHas('banners', ['page' => 'rooms', 'heading' => 'Rooms Only Heading']);
        $this->assertDatabaseHas('banners', ['page' => 'home', 'heading' => 'Amed Escape']);
        $this->assertDatabaseHas('banners', ['page' => 'spa', 'heading' => 'Amed Escape']);

        $this->get('/room')->assertSee('Rooms Only Heading');
        $this->get('/')->assertDontSee('Rooms Only Heading');
        $this->get('/spa')->assertDontSee('Rooms Only Heading');
    }

    // ---- Public rendering per page ----

    public function test_each_public_page_shows_its_own_banner(): void
    {
        Banner::where('page', 'home')->update(['heading' => 'Home Banner']);
        Banner::where('page', 'rooms')->update(['heading' => 'Rooms Banner']);

        $this->get('/')->assertSee('Home Banner');
        $this->get('/room')->assertSee('Rooms Banner');
        $this->get('/room')->assertDontSee('Home Banner');
    }

    public function test_inactive_banner_hides_it_on_that_page_only(): void
    {
        Banner::where('page', 'spa')->update(['is_active' => false, 'heading' => 'Spa Special']);
        Banner::where('page', 'home')->update(['heading' => 'Home Special']);

        $this->get('/spa')->assertDontSee('Spa Special');
        $this->get('/')->assertSee('Home Special');
    }

    public function test_activity_page_now_shows_its_banner(): void
    {
        Banner::where('page', 'activities')->update(['heading' => 'Activity Banner Heading']);

        $response = $this->get('/activity');

        $response->assertOk();
        $response->assertSee('Activity Banner Heading');
    }

    public function test_discover_more_hidden_when_link_empty(): void
    {
        Banner::where('page', 'home')->update(['link_url' => null]);

        $response = $this->get('/');

        preg_match('/<section class="promo-banner">.*?<\/section>/s', $response->getContent(), $matches);
        $this->assertNotEmpty($matches);
        $this->assertStringNotContainsString('rule-link', $matches[0]);
    }

    public function test_discover_more_shown_when_link_set(): void
    {
        Banner::where('page', 'home')->update(['link_url' => 'https://example.com/deal']);

        $response = $this->get('/');

        $response->assertSee('Discover More');
        $response->assertSee('https://example.com/deal', false);
    }

    // ---- Image deletion safety ----

    public function test_replacing_an_image_does_not_delete_a_file_used_by_another_banner(): void
    {
        $admin = $this->admin();
        Storage::disk('public')->put('banners/shared.png', 'fake-image-content');
        Banner::where('page', 'rooms')->update(['background_image' => 'banners/shared.png']);
        Banner::where('page', 'spa')->update(['background_image' => 'banners/shared.png']);

        $this->actingAs($admin)->put('/admin/banners/rooms', [
            'eyebrow' => 'Explore', 'heading' => 'Amed Escape', 'body' => 'Body.',
            'background_image' => UploadedFile::fake()->image('new-rooms.jpg'), 'is_active' => '1',
        ])->assertRedirect();

        Storage::disk('public')->assertExists('banners/shared.png');
        $this->assertSame('banners/shared.png', Banner::where('page', 'spa')->first()->background_image);
    }

    public function test_replacing_an_image_never_deletes_legacy_images_path_files(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->put('/admin/banners/home', [
            'eyebrow' => 'Explore', 'heading' => 'Amed Escape', 'body' => 'Body.',
            'background_image' => UploadedFile::fake()->image('new-home.jpg'), 'is_active' => '1',
        ])->assertRedirect();

        // The legacy path was never a Storage-managed file, so nothing on disk to delete —
        // simply confirm the real, non-fake public file still exists.
        $this->assertFileExists(public_path('images/home/promo-amed-escape.png'));
    }

    // ---- Validation (same as the old Promo Banner form) ----

    public function test_admin_can_edit_and_save_banner(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)->put('/admin/banners/home', [
            'eyebrow' => 'New Eyebrow', 'heading' => 'New Heading', 'body' => 'New body.',
            'link_url' => 'https://example.com/deal', 'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.banners.edit', 'home'));
        $this->assertDatabaseHas('banners', ['page' => 'home', 'heading' => 'New Heading']);
    }

    public function test_required_fields_validated(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->put('/admin/banners/home', [])
            ->assertSessionHasErrors(['eyebrow', 'heading', 'body']);
    }

    // ---- Data migration result ----

    public function test_data_migration_produces_seven_rows_with_expected_sources(): void
    {
        // setUp() seeds a synthetic fixture (same image everywhere) for the other tests'
        // convenience — run the real seeder here to verify its actual, real output.
        $this->seed(\Database\Seeders\BannerSeeder::class);

        $this->assertSame(7, Banner::count());

        $this->assertSame('/images/home/promo-amed-escape.png', Banner::where('page', 'home')->first()->background_image);
        $this->assertSame('/images/rooms/promo-amed-escape.png', Banner::where('page', 'rooms')->first()->background_image);
        // Activity had no banner before — copies Home's image, not a per-page one.
        $this->assertSame('/images/home/promo-amed-escape.png', Banner::where('page', 'activities')->first()->background_image);
        $this->assertSame('/images/spa/promo-amed-escape.png', Banner::where('page', 'spa')->first()->background_image);
        $this->assertSame('/images/contact/promo-amed-escape.png', Banner::where('page', 'contact')->first()->background_image);
        $this->assertSame('/images/dining/resto-amed-cafe/promo-amed-escape.png', Banner::where('page', 'resto-amed-cafe')->first()->background_image);
        $this->assertSame('/images/dining/barak-rooftop-and-bar/promo-amed-escape.png', Banner::where('page', 'barak-rooftop-and-bar')->first()->background_image);
    }
}
