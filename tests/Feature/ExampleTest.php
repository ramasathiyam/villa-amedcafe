<?php

namespace Tests\Feature;

use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        Page::create([
            'slug' => 'home',
            'hero_title' => 'Test Hero',
            'intro_heading' => 'Test Intro',
            'intro_body' => 'Test body.',
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
