<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HomeActivityCardLinksTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Page::create(['slug' => 'home', 'hero_title' => 'Home', 'intro_heading' => 'Home', 'intro_body' => 'Body.']);
        Page::create(['slug' => 'activities', 'hero_title' => 'Activity', 'intro_heading' => 'Activity', 'intro_body' => 'Body.']);
    }

    private function makeActivity(array $overrides = []): Activity
    {
        return Activity::create(array_merge([
            'name' => 'Test Activity',
            'is_active' => true,
            'show_on_home' => true,
            'show_on_activity_page' => false,
            'sort_order' => 0,
        ], $overrides));
    }

    // ---- Home card links ----

    public function test_home_card_links_to_activity_anchor_when_visible_on_activity_page(): void
    {
        $this->makeActivity(['name' => 'Writing on Lontar', 'show_on_activity_page' => true]);

        $content = $this->get('/')->assertOk()->getContent();

        $expected = route('activity').'#activity-writing-on-lontar';
        $this->assertStringContainsString('href="'.$expected.'"', $content);
    }

    public function test_home_card_links_to_activity_page_without_hash_when_not_on_activity_page(): void
    {
        $this->makeActivity(['name' => 'Wellness', 'show_on_activity_page' => false]);

        $content = $this->get('/')->getContent();

        preg_match('/<a[^>]*class="activity-card-title-link"[^>]*>Wellness<\/a>/', $content, $link);
        $this->assertNotEmpty($link, 'Wellness title link not found');
        $this->assertStringContainsString('href="'.route('activity').'"', $link[0]);
        $this->assertStringNotContainsString('#', $link[0]);
    }

    public function test_home_card_title_link_present_and_image_link_has_tabindex_and_aria_hidden(): void
    {
        $this->makeActivity(['name' => 'Fishing', 'image' => '/images/activities/fishing.png']);

        $content = $this->get('/')->getContent();

        preg_match('/<a[^>]*class="activity-card-title-link"[^>]*>Fishing<\/a>/', $content, $titleLink);
        $this->assertNotEmpty($titleLink, 'title link missing');
        $this->assertStringNotContainsString('tabindex', $titleLink[0]);
        $this->assertStringNotContainsString('aria-hidden', $titleLink[0]);

        preg_match('/<a[^>]*class="activity-card-image-link"[^>]*>/', $content, $imageLink);
        $this->assertNotEmpty($imageLink, 'image link missing');
        $this->assertStringContainsString('tabindex="-1"', $imageLink[0]);
        $this->assertStringContainsString('aria-hidden="true"', $imageLink[0]);
    }

    // ---- Activity page anchors ----

    public function test_activity_page_renders_matching_unique_ids(): void
    {
        $this->makeActivity(['name' => 'Snorkeling Trip', 'show_on_home' => false, 'show_on_activity_page' => true]);
        $this->makeActivity(['name' => 'Fishing Trip & BBQ', 'show_on_home' => false, 'show_on_activity_page' => true]);

        $content = $this->get('/activity')->assertOk()->getContent();

        preg_match_all('/id="(activity-[a-z0-9-]+)"/', $content, $ids);
        $this->assertCount(2, $ids[1]);
        $this->assertSame($ids[1], array_unique($ids[1]), 'Activity page ids are not unique');
        $this->assertContains('activity-snorkeling-trip', $ids[1]);
        $this->assertContains('activity-fishing-trip-bbq', $ids[1]);
    }

    // ---- Query efficiency ----

    public function test_no_extra_queries_per_home_activity_card(): void
    {
        $queryCount = 0;
        DB::listen(function () use (&$queryCount) {
            $queryCount++;
        });

        $countActivityQueriesFor = function (int $count) use (&$queryCount): int {
            Activity::query()->delete();
            for ($i = 0; $i < $count; $i++) {
                $this->makeActivity(['name' => 'Activity '.$i, 'show_on_activity_page' => (bool) ($i % 2)]);
            }

            $queryCount = 0;
            $this->get('/')->assertOk();

            return $queryCount;
        };

        $withOne = $countActivityQueriesFor(1);
        $withSix = $countActivityQueriesFor(6);

        $this->assertSame($withOne, $withSix, 'Query count grew with activity card count — linkUrl must not add per-card queries');
    }
}
