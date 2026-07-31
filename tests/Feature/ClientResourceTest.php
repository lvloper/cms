<?php

namespace Tests\Feature;

use App\Enums\Status;
use App\Models\Client;
use App\Models\Route;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class ClientResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_page_is_available_under_client_prefix(): void
    {
        $client = Client::create([
            'popup_text_color' => 'black',
            'blocks' => [],
            'works' => [
                [
                    'title' => 'Sitio institucional',
                    'categories' => ['design', 'programming'],
                    'external_url' => 'https://example.com',
                ],
            ],
            'testimonials' => [
                [
                    'person' => 'Ada Lovelace',
                    'position' => 'CTO',
                    'testimonial' => 'Un gran equipo.',
                ],
            ],
            'preview_items' => [],
        ]);

        $route = Route::create([
            'title' => 'Acme',
            'slug' => 'acme',
            'full_slug' => 'cliente/acme',
            'status' => Status::Published,
            'routable_type' => Client::class,
            'routable_id' => $client->id,
        ]);

        $this->assertSame('cliente/acme', $route->fresh()->full_slug);
        $this->assertTrue($route->fresh()->status === Status::Published);
        $this->assertInstanceOf(Client::class, $route->fresh()->routable);
        $this->assertNotNull(Route::whereFullSlug('cliente/acme')->first());

        $response = $this->get('/cliente/acme');

        $response
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Cms/Client', false)
                ->where('client.title', 'Acme')
                ->where('client.works.0.title', 'Sitio institucional')
                ->where('client.works.0.categories', ['Diseño', 'Programación'])
                ->where('client.testimonials.0.person', 'Ada Lovelace')
                ->has('blocks', 0));

        $this->assertSame('black', $client->fresh()->popup_text_color);
    }

    public function test_client_json_fields_are_cast_to_collections(): void
    {
        $client = Client::create([
            'blocks' => [],
            'works' => [],
            'testimonials' => [],
            'preview_items' => [],
        ]);

        $this->assertInstanceOf(Collection::class, $client->blocks);
        $this->assertInstanceOf(Collection::class, $client->works);
        $this->assertInstanceOf(Collection::class, $client->testimonials);
        $this->assertInstanceOf(Collection::class, $client->preview_items);
    }

    public function test_client_navigation_wraps_between_published_clients(): void
    {
        $this->createClientWithRoute('Primero', 'primero', 10);
        $this->createClientWithRoute('Segundo', 'segundo', 20);
        $this->createClientWithRoute('Último', 'ultimo', 30);

        $this->get('/cliente/primero')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('client.navigation.previous.title', 'Último')
                ->where('client.navigation.previous.url', url('/cliente/ultimo'))
                ->where('client.navigation.next.title', 'Segundo')
                ->where('client.navigation.next.url', url('/cliente/segundo'))
                ->where('client.navigation.next.color', '#123456')
                ->where('client.navigation.next.popupTextColor', 'black')
                ->has('client.navigation.next.previewItems', 0));

        $this->get('/cliente/ultimo')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('client.navigation.previous.title', 'Segundo')
                ->where('client.navigation.next.title', 'Primero'));

    }

    private function createClientWithRoute(string $title, string $slug, int $sortOrder): Client
    {
        $client = Client::create([
            'blocks' => [],
            'works' => [],
            'testimonials' => [],
            'preview_items' => [],
            'sort_order' => $sortOrder,
            'color' => '#123456',
            'popup_text_color' => 'black',
        ]);

        Route::create([
            'title' => $title,
            'slug' => $slug,
            'full_slug' => 'cliente/'.$slug,
            'status' => Status::Published,
            'routable_type' => Client::class,
            'routable_id' => $client->id,
        ]);

        return $client;
    }
}
