<?php

namespace Tests\Feature;

use App\Models\Client;
use Database\Seeders\AmnistiaClientCaseSeeder;
use Database\Seeders\ClientSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class AmnistiaClientCaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_builds_the_reference_case_with_generic_client_blocks(): void
    {
        $this->seed(ClientSeeder::class);
        $this->seed(AmnistiaClientCaseSeeder::class);

        $client = Client::query()
            ->whereHas('route', fn ($query) => $query->where('slug', 'amnistia-internacional'))
            ->firstOrFail();

        $types = $client->blocks->pluck('type')->all();

        $this->assertSame('Tecnología para acompañar la acción', $client->hero_title);
        $this->assertCount(9, $types);
        $this->assertSame([
            'ClientMarquee',
            'ClientFeature',
            'ClientProjects',
            'ClientFeature',
            'ClientStatement',
            'ClientProcess',
            'ClientMetrics',
            'ClientTestimonial',
            'ClientClosing',
        ], $types);
        $this->assertTrue(collect($types)->every(fn (string $type): bool => str_starts_with($type, 'Client')));
        $this->assertNotContains('ClientMediaGrid', $types);
        $this->assertSame('text_left', $client->blocks[1]['data']['layout']);
        $this->assertSame('text_right', $client->blocks[3]['data']['layout']);
        $this->assertGreaterThan(40, strlen($client->blocks[0]['data']['items'][0]));
        $this->assertStringContainsString('El equipo lideró con gran profesionalismo', $client->blocks[7]['data']['testimonials'][0]['quote']);
        $this->assertStringContainsString('Nos alegra haber contado con un equipo tan comprometido y profesional.', $client->blocks[7]['data']['testimonials'][1]['quote']);
        $this->assertArrayNotHasKey('media_type', $client->blocks[7]['data']['testimonials'][0]);
        $this->assertStringStartsWith('Reemplazar por imagen/video', $client->hero_media_placeholder);
    }

    public function test_the_reference_case_is_exposed_after_the_own_client_hero(): void
    {
        $this->seed(ClientSeeder::class);
        $this->seed(AmnistiaClientCaseSeeder::class);

        $this->get('/cliente/amnistia-internacional')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Cms/Client', false)
                ->where('client.hero_title', 'Tecnología para acompañar la acción')
                ->where('client.relationship_since', 'Desde 2018')
                ->has('client.hero_services', 6)
                ->has('blocks', 9)
                ->where('blocks.0.type', 'ClientMarquee')
                ->where('blocks.8.type', 'ClientClosing'));
    }

    public function test_the_reference_case_seeder_is_idempotent(): void
    {
        $this->seed(ClientSeeder::class);
        $this->seed(AmnistiaClientCaseSeeder::class);
        $this->seed(AmnistiaClientCaseSeeder::class);

        $this->assertSame(1, Client::query()
            ->whereHas('route', fn ($query) => $query->where('slug', 'amnistia-internacional'))
            ->count());
    }
}
