<?php

namespace Tests\Feature;

use App\Models\DiningVenue;
use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HeritageBlockDiscoverMoreTest extends TestCase
{
    use RefreshDatabase;

    private function makePage(string $slug): Page
    {
        return Page::create([
            'slug' => $slug,
            'hero_title' => 'Title',
            'intro_heading' => 'Intro',
            'intro_body' => 'Body copy.',
        ]);
    }

    private function makeDiningVenue(string $slug, string $name): DiningVenue
    {
        return DiningVenue::create([
            'slug' => $slug,
            'name' => $name,
            'is_active' => true,
            'hero_title' => $name,
            'hero_image' => '/images/dining/placeholder.png',
            'intro_heading' => $name,
            'intro_body' => 'Body.',
        ]);
    }

    public function test_home_heritage_block_discover_more_is_an_enabled_link_to_activity_page(): void
    {
        $this->makePage('home');

        $content = $this->get('/')->assertOk()->getContent();

        preg_match('/<section class="feature-block[^"]*">.*?<\/section>/s', $content, $block);
        $this->assertNotEmpty($block, 'heritage feature-block not found on Home');

        $this->assertStringContainsString('Amed Café &amp; Hotel Kebun Wayan', $block[0]);
        preg_match('/<a[^>]*href="[^"]*"[^>]*>\s*<span[^>]*><\/span>\s*Discover More/s', $block[0], $link);
        $this->assertNotEmpty($link, 'Discover More is not rendered as an enabled <a> link on Home');
        $this->assertStringContainsString('href="'.route('activity').'"', $link[0]);
        $this->assertStringNotContainsString('target="_blank"', $link[0]);
        $this->assertStringNotContainsString('aria-disabled', $block[0]);
    }

    private function assertAllFeatureBlocksLinkToActivity(string $content, int $expectedCount, string $label): void
    {
        preg_match_all('/<section class="feature-block[^"]*">.*?<\/section>/s', $content, $blocks);
        $heritageBlocks = array_values(array_filter(
            $blocks[0],
            fn (string $block) => str_contains($block, 'Amed Café &amp; Hotel Kebun Wayan')
        ));

        $this->assertCount($expectedCount, $heritageBlocks, "Expected {$expectedCount} heritage feature-block(s) on {$label}");

        foreach ($heritageBlocks as $block) {
            preg_match('/<a[^>]*href="[^"]*"[^>]*>\s*<span[^>]*><\/span>\s*Discover More/s', $block, $link);
            $this->assertNotEmpty($link, "Discover More is not an enabled <a> link in a heritage block on {$label}");
            $this->assertStringContainsString('href="'.route('activity').'"', $link[0]);
            $this->assertStringNotContainsString('target="_blank"', $link[0]);
            $this->assertStringNotContainsString('aria-disabled', $block);
            $this->assertStringNotContainsString('rule-link-disabled', $block);
        }
    }

    public function test_spa_heritage_blocks_have_two_enabled_discover_more_links(): void
    {
        $this->makePage('spa');

        $content = $this->get('/spa')->assertOk()->getContent();

        $this->assertAllFeatureBlocksLinkToActivity($content, 2, 'Spa');
    }

    public function test_resto_amed_cafe_heritage_blocks_have_two_enabled_discover_more_links(): void
    {
        $this->makePage('resto-amed-cafe');
        $this->makeDiningVenue('resto-amed-cafe', 'Resto Amed Cafe');

        $content = $this->get('/dining/resto-amed-cafe')->assertOk()->getContent();

        $this->assertAllFeatureBlocksLinkToActivity($content, 2, 'Resto Amed Cafe');
    }

    public function test_no_about_page_built_yet_reason_remains_on_home_spa_or_resto(): void
    {
        $this->makePage('home');
        $this->makePage('spa');
        $this->makePage('resto-amed-cafe');
        $this->makeDiningVenue('resto-amed-cafe', 'Resto Amed Cafe');

        $this->get('/')->assertDontSee('No About page built yet');
        $this->get('/spa')->assertDontSee('No About page built yet');
        $this->get('/dining/resto-amed-cafe')->assertDontSee('No About page built yet');
    }

    public function test_barak_rooftop_and_bar_has_no_heritage_block_and_is_unaffected(): void
    {
        $this->makePage('barak-rooftop-and-bar');
        $this->makeDiningVenue('barak-rooftop-and-bar', 'Barak Rooftop and Bar');

        $response = $this->get('/dining/barak-rooftop-and-bar');

        $response->assertOk();
        $response->assertDontSee('No About page built yet');
        $response->assertDontSee(route('activity'), false);
    }
}
