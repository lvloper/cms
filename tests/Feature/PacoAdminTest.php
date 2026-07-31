<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Resources\PacoPlaybookResource\Pages\ManagePacoPlaybooks;
use App\Models\Paco\Playbook;
use App\Models\User;
use Database\Seeders\PacoBootstrapSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class PacoAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_conversation_resources_render_for_an_authenticated_editor(): void
    {
        $this->seed(PacoBootstrapSeeder::class);
        $this->actingAs(User::factory()->create());

        foreach ([
            '/admin/paco-leads',
            '/admin/paco-campaigns',
            '/admin/paco-intents',
            '/admin/paco-playbooks',
            '/admin/paco-questions',
            '/admin/paco-response-blocks',
            '/admin/paco-service-fit-rules',
        ] as $path) {
            $this->get($path)->assertOk();
        }
    }

    public function test_playbook_edit_form_renders_questions_without_short_prompts(): void
    {
        $this->seed(PacoBootstrapSeeder::class);
        $this->actingAs(User::factory()->create());

        $playbook = Playbook::query()
            ->whereHas('fields.question', fn ($query) => $query->whereNull('short_prompt'))
            ->firstOrFail();

        Livewire::test(ManagePacoPlaybooks::class)
            ->mountTableAction('edit', $playbook)
            ->assertSet('mountedActions.0.name', 'edit');
    }
}
