<?php

namespace Tests\Feature;

use App\Enums\Status;
use App\Models\Client;
use App\Models\Route;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class HomeClientLogosTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_exposes_only_published_clients_with_logos(): void
    {
        $visible = $this->createClient(
            logo: 'images/clients/logos/visible.webp',
            testimonials: [
                [
                    'person' => 'Ada Lovelace',
                    'position' => 'CTO',
                    'testimonial' => '<p>Un <strong>gran</strong> equipo.</p>',
                ],
                [
                    'person' => 'Grace Hopper',
                    'position' => 'Admiral',
                    'testimonial' => '<p>Este testimonio no debe mostrarse.</p>',
                ],
            ],
            previewItems: [
                [
                    'type' => 'video',
                    'file' => 'media/clients/previews/example.mp4',
                    'duration_ms' => 2500,
                ],
                [
                    'type' => 'testimonial',
                    'duration_ms' => null,
                ],
                [
                    'type' => 'image',
                    'file' => 'media/clients/previews/example.webp',
                    'duration_ms' => null,
                ],
            ],
        );
        $this->createRoute($visible, 'Visible', Status::Published);

        $draft = $this->createClient('images/clients/logos/draft.webp');
        $this->createRoute($draft, 'Draft', Status::Draft);

        $withoutLogo = $this->createClient();
        $this->createRoute($withoutLogo, 'Sin logo', Status::Published);

        $notFeatured = $this->createClient('images/clients/logos/not-featured.webp', false);
        $this->createRoute($notFeatured, 'No destacado', Status::Published);

        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Home', false)
                ->has('clients', 1)
                ->where('clients.0.id', $visible->id)
                ->where('clients.0.alt', 'Visible')
                ->where('clients.0.title', 'Visible')
                ->where('clients.0.color', '#123456')
                ->where('clients.0.popupTextColor', 'white')
                ->where('clients.0.testimonial.text', 'Un gran equipo.')
                ->where('clients.0.testimonial.person', 'Ada Lovelace')
                ->where('clients.0.testimonial.position', 'CTO')
                ->where('clients.0.previewItems.0.type', 'video')
                ->where('clients.0.previewItems.0.durationMs', 2500)
                ->where('clients.0.previewItems.1.type', 'testimonial')
                ->where('clients.0.previewItems.1.content.text', 'Un gran equipo.')
                ->where('clients.0.previewItems.1.durationMs', null)
                ->where('clients.0.previewItems.2.type', 'image')
                ->where('clients.0.previewItems.2.durationMs', null));
    }

    public function test_home_orders_clients_by_sort_order(): void
    {
        $last = $this->createClient('images/clients/logos/last.webp', sortOrder: 20);
        $this->createRoute($last, 'Último', Status::Published);

        $first = $this->createClient('images/clients/logos/first.webp', sortOrder: 10);
        $this->createRoute($first, 'Primero', Status::Published);

        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('clients.0.id', $first->id)
                ->where('clients.1.id', $last->id));
    }

    /**
     * @param  array<int, array<string, string>>  $testimonials
     * @param  array<int, array<string, mixed>>  $previewItems
     */
    private function createClient(
        ?string $logo = null,
        bool $isFeatured = true,
        array $testimonials = [],
        array $previewItems = [],
        int $sortOrder = 0,
    ): Client {
        return Client::create([
            'logo' => $logo,
            'color' => '#123456',
            'sort_order' => $sortOrder,
            'popup_text_color' => 'white',
            'is_featured' => $isFeatured,
            'blocks' => [],
            'works' => [],
            'testimonials' => $testimonials,
            'preview_items' => $previewItems,
        ]);
    }

    private function createRoute(Client $client, string $title, Status $status): Route
    {
        return Route::create([
            'title' => $title,
            'slug' => 'client-'.$client->getKey(),
            'full_slug' => 'cliente/client-'.$client->getKey(),
            'status' => $status,
            'routable_type' => Client::class,
            'routable_id' => $client->id,
        ]);
    }
}
