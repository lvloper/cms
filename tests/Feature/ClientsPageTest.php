<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class ClientsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_clients_page_is_available_as_an_inertia_page(): void
    {
        $this->get('/clientes')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Clients', false)
                ->has('clients', 0));
    }
}
