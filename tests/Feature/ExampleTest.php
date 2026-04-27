<?php

namespace Tests\Feature;

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
        // In a fresh database there is no home route, so the app returns 404.
        // Once a home page is created this would return 200.
        $response = $this->get('/');

        $response->assertStatus(404);
    }
}
