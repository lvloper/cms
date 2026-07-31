<?php

namespace Tests\Feature;

use App\Models\Client;
use Database\Seeders\ClientSeeder;
use Database\Seeders\ClientWorksDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientWorksDemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_populates_empty_clients_with_two_demo_works(): void
    {
        $this->seed(ClientSeeder::class);

        $this->seed(ClientWorksDemoSeeder::class);

        $clients = Client::query()->get();

        $this->assertCount(7, $clients);
        $this->assertSame(14, $clients->sum(fn (Client $client): int => $client->works->count()));
        $this->assertTrue($clients->every(fn (Client $client): bool => $client->works->every(
            fn (array $work): bool => str_starts_with($work['title'], 'Demo —')
                && array_key_exists('image', $work)
                && filter_var($work['external_url'], FILTER_VALIDATE_URL) !== false,
        )));
    }

    public function test_it_is_idempotent_and_preserves_real_work(): void
    {
        $this->seed(ClientSeeder::class);
        $this->seed(ClientWorksDemoSeeder::class);

        $client = Client::query()->firstOrFail();
        $client->update([
            'works' => [[
                'title' => 'Trabajo real cargado desde el CMS',
                'categories' => ['programming'],
                'external_url' => 'https://example.com/trabajo-real',
                'image' => null,
                'description' => 'Este registro no debe ser reemplazado por el seeder demo.',
            ]],
        ]);

        $this->seed(ClientWorksDemoSeeder::class);

        $this->assertSame('Trabajo real cargado desde el CMS', $client->fresh()->works->first()['title']);
        $this->assertSame(13, Client::query()->get()->sum(fn (Client $item): int => $item->works->count()));
    }
}
