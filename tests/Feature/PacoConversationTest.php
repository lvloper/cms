<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Contracts\Paco\PacoModelGateway;
use App\Enums\Status;
use App\Jobs\Paco\SendLeadNotification;
use App\Mail\PacoLeadReceived;
use App\Models\Client;
use App\Models\Paco\Campaign;
use App\Models\Paco\ContentImpression;
use App\Models\Paco\Conversation;
use App\Models\Paco\Intent;
use App\Models\Paco\Lead;
use App\Models\Paco\Playbook;
use App\Models\Paco\Question;
use App\Models\Route;
use App\Services\Paco\DeterministicPacoModelGateway;
use App\Services\Paco\OpenCodeGoModelGateway;
use App\Services\Paco\PacoPrefillService;
use Database\Seeders\PacoBootstrapSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\TestCase;

final class PacoConversationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PacoBootstrapSeeder::class);
    }

    public function test_bootstrap_is_idempotent_and_preserves_editorial_text(): void
    {
        Question::query()->where('code', 'landing_goal')->update(['prompt' => 'Texto editado']);

        $this->seed(PacoBootstrapSeeder::class);

        self::assertSame(14, Intent::query()->count());
        self::assertSame(7, Playbook::query()->count());
        self::assertSame(5, Campaign::query()->count());
        self::assertSame('Texto editado', Question::query()->where('code', 'landing_goal')->value('prompt'));
    }

    public function test_landing_conversation_reaches_pending_review(): void
    {
        Queue::fake();

        $created = $this->postJson('/api/paco/conversations', [
            'campaign' => 'home_default',
            'locale' => 'es-AR',
        ])->assertCreated()
            ->assertJsonPath('version', 1)
            ->assertJsonPath('turn.parts.0.type', 'text_input')
            ->json();

        $id = $created['conversation_id'];
        $token = $created['conversation_token'];

        $first = $this->action($id, $token, 1, [
            'type' => 'text_submit',
            'component_id' => 'initial_need',
            'value' => 'Necesitamos una landing para una campaña de donaciones.',
        ])->assertOk()
            ->assertJsonPath('stage', 'understanding_need')
            ->assertJsonPath('turn.parts.0.id', 'landing_goal')
            ->json();

        $second = $this->action($id, $token, $first['version'], [
            'type' => 'single_select',
            'component_id' => 'landing_goal',
            'value' => 'campaign',
        ])->assertOk()
            ->assertJsonPath('stage', 'contact_required')
            ->assertJsonPath('turn.message', 'Ya tenemos un buen punto de partida. Para continuar y poder responderte, compartinos tus datos de contacto.')
            ->assertJsonPath('turn.parts.0.type', 'contact_form')
            ->json();

        $third = $this->action($id, $token, $second['version'], [
            'type' => 'contact_submit',
            'component_id' => 'contact',
            'value' => [
                'name' => 'Ana',
                'channel' => 'email',
                'contact_value' => 'ana@example.com',
            ],
        ])->assertOk()
            ->assertJsonPath('stage', 'qualifying')
            ->assertJsonPath('turn.parts.0.id', 'content_readiness')
            ->json();

        $fourth = $this->action($id, $token, $third['version'], [
            'type' => 'single_select',
            'component_id' => 'content_readiness',
            'value' => [
                'choice' => 'partial',
                'detail' => 'Tenemos los textos, pero todavía faltan las fotos.',
            ],
        ])->assertOk()
            ->assertJsonPath('status', 'active')
            ->assertJsonPath('turn.parts.0.id', 'contact_context')
            ->assertJsonPath('turns.7.message', 'Tenemos una parte — Tenemos los textos, pero todavía faltan las fotos.')
            ->json();

        $fifth = $this->action($id, $token, $fourth['version'], [
            'type' => 'single_select',
            'component_id' => 'contact_context',
            'value' => 'owner',
        ])->assertOk()
            ->assertJsonPath('status', 'active')
            ->assertJsonPath('turn.parts.0.id', 'organization_structure')
            ->json();

        $this->action($id, $token, $fifth['version'], [
            'type' => 'single_select',
            'component_id' => 'organization_structure',
            'value' => 'organization',
        ])->assertOk()
            ->assertJsonPath('status', 'closed')
            ->assertJsonPath('stage', 'closed_pending_review')
            ->assertJsonPath('turn.meta.objective', 'close_sufficient');

        $lead = Lead::query()->firstOrFail();
        self::assertSame('pending_review', $lead->status);
        self::assertSame('ana@example.com', $lead->email);
        self::assertSame('landing_page', $lead->primary_intent_code);
        self::assertSame([
            'choice' => 'partial',
            'detail' => 'Tenemos los textos, pero todavía faltan las fotos.',
        ], $lead->attributes()->where('field_code', 'content_readiness')->firstOrFail()->value_json);
        self::assertNotNull($lead->score);
        Queue::assertPushed(SendLeadNotification::class, fn (SendLeadNotification $job): bool => $job->leadId === $lead->id);
    }

    public function test_client_closing_conversation_uses_the_client_message_and_records_its_origin(): void
    {
        $client = Client::query()->create([
            'paco_closing_message' => 'Hola, ¿te gustaría hacer algo parecido para tu organización? Contanos tu caso.',
        ]);

        $created = $this->postJson('/api/paco/conversations', [
            'campaign' => 'direct_default',
            'page_context' => [
                'content_type' => 'client',
                'content_id' => $client->id,
            ],
        ])->assertCreated()
            ->assertJsonPath('turn.message', 'Hola, ¿te gustaría hacer algo parecido para tu organización? Contanos tu caso.')
            ->json();

        self::assertSame(
            $client->id,
            Conversation::query()->findOrFail($created['conversation_id'])->source_client_id,
        );
    }

    public function test_contact_is_followed_by_authorized_commercial_evidence_with_visible_client_name(): void
    {
        $client = $this->createEvidenceClient('Fundación Huésped', 'Organizaciones sociales y salud', [
            'title' => 'Landing de campaña de donaciones',
            'categories' => ['marketing', 'design', 'programming'],
            'external_url' => 'https://example.com/casos/huesped-donaciones',
            'problem' => 'Comunicar una campaña y facilitar donaciones.',
            'solution' => 'Diseñamos y desarrollamos una landing con contenidos jerarquizados.',
            'result' => 'La organización pudo publicar la campaña en una experiencia administrable.',
            'tags' => 'landing, campaña, donaciones, ONG',
            'use_authorized' => true,
            'chat_enabled' => true,
        ]);
        $client->forceFill(['testimonials' => [[
            'person' => 'Florencia Gadea',
            'position' => 'Directora de División de Comunicación',
            'short_quote' => 'Destaco la flexibilidad y la buena predisposición del equipo.',
            'testimonial' => 'Destaco la flexibilidad y la buena predisposición del equipo para llevar adelante el proyecto.',
            'use_authorized' => true,
            'chat_enabled' => true,
        ]]])->save();

        $created = $this->postJson('/api/paco/conversations')->assertCreated()->json();
        $first = $this->action($created['conversation_id'], $created['conversation_token'], 1, [
            'type' => 'text_submit', 'component_id' => 'initial_need',
            'value' => 'Necesitamos una landing para la campaña de donaciones de una ONG.',
        ])->assertOk()->json();
        $second = $this->action($created['conversation_id'], $created['conversation_token'], $first['version'], [
            'type' => 'single_select', 'component_id' => 'landing_goal', 'value' => 'campaign',
        ])->assertOk()->json();

        $afterContact = $this->action($created['conversation_id'], $created['conversation_token'], $second['version'], [
            'type' => 'contact_submit', 'component_id' => 'contact',
            'value' => ['name' => 'Ana', 'channel' => 'email', 'contact_value' => 'ana@example.com'],
        ])->assertOk()
            ->assertJsonPath('stage', 'qualifying')
            ->assertJsonPath('turn.parts.0.id', 'content_readiness')
            ->assertJsonPath('turn.meta.question_text', '¿Ya tienen definidos los contenidos?')
            ->json();

        $response = $this->action($created['conversation_id'], $created['conversation_token'], $afterContact['version'], [
            'type' => 'single_select', 'component_id' => 'content_readiness', 'value' => 'ready',
        ])->assertOk()
            ->assertJsonPath('stage', 'trust_building')
            ->assertJsonPath('turn.parts.0.type', 'content_carousel')
            ->assertJsonPath('turn.parts.0.items.0.entity_type', 'work')
            ->assertJsonPath('turn.parts.0.items.0.client_name', 'Fundación Huésped')
            ->assertJsonPath('turn.parts.1.id', 'contact_context');

        self::assertStringContainsString('Fundación Huésped', (string) $response->json('turn.message'));
        self::assertStringContainsString('Landing de campaña de donaciones', (string) $response->json('turn.message'));
        self::assertStringContainsString('Florencia Gadea, Directora de División de Comunicación de Fundación Huésped', (string) $response->json('turn.message'));
        self::assertStringContainsString('Destaco la flexibilidad y la buena predisposición del equipo.', (string) $response->json('turn.message'));
        self::assertSame('testimonial', $response->json('turn.parts.0.items.1.entity_type'));
        self::assertSame('same_problem_same_industry', ContentImpression::query()->firstOrFail()->reason_code);
    }

    public function test_unauthorized_evidence_is_never_exposed_or_mentioned_to_the_visitor(): void
    {
        $this->createEvidenceClient('Cliente privado', 'Organizaciones sociales', [
            'title' => 'Landing privada',
            'categories' => ['marketing'],
            'external_url' => 'https://example.com/privado',
            'solution' => 'Texto que nunca debe llegar al chat.',
            'tags' => 'landing, ONG',
            'use_authorized' => false,
            'chat_enabled' => true,
        ]);

        $created = $this->postJson('/api/paco/conversations')->assertCreated()->json();
        $first = $this->action($created['conversation_id'], $created['conversation_token'], 1, [
            'type' => 'text_submit', 'component_id' => 'initial_need',
            'value' => 'Necesitamos una landing para una ONG.',
        ])->assertOk()->json();
        $second = $this->action($created['conversation_id'], $created['conversation_token'], $first['version'], [
            'type' => 'single_select', 'component_id' => 'landing_goal', 'value' => 'campaign',
        ])->assertOk()->json();

        $afterContact = $this->action($created['conversation_id'], $created['conversation_token'], $second['version'], [
            'type' => 'contact_submit', 'component_id' => 'contact',
            'value' => ['name' => 'Ana', 'channel' => 'email', 'contact_value' => 'ana@example.com'],
        ])->assertOk()
            ->assertJsonPath('turn.parts.0.id', 'content_readiness')
            ->json();

        $response = $this->action($created['conversation_id'], $created['conversation_token'], $afterContact['version'], [
            'type' => 'single_select', 'component_id' => 'content_readiness', 'value' => 'ready',
        ])->assertOk();

        self::assertStringContainsString('Gracias, Ana. Para entender mejor tu caso:', (string) $response->json('turn.message'));
        self::assertStringNotContainsString('caso publicable', (string) $response->json('turn.message'));
        self::assertStringNotContainsString('Cliente privado', (string) $response->json('turn.message'));
        self::assertSame('contact_context', $response->json('turn.parts.0.id'));
        self::assertSame(0, ContentImpression::query()->count());
    }

    public function test_commercial_evidence_can_show_several_authorized_projects_in_one_argument(): void
    {
        foreach (['Fundación Huésped', 'Amnistía Internacional', 'CEDES'] as $clientName) {
            $this->createEvidenceClient($clientName, 'Organizaciones sociales', [
                'title' => "Sitio institucional de {$clientName}",
                'problem' => 'Ordenar contenidos y mejorar la experiencia del sitio institucional.',
                'solution' => 'Diseñamos y desarrollamos una experiencia web administrable.',
                'tags' => 'web, sitio institucional, ONG',
                'use_authorized' => true,
                'chat_enabled' => true,
            ]);
        }

        $created = $this->postJson('/api/paco/conversations')->assertCreated()->json();
        $first = $this->action($created['conversation_id'], $created['conversation_token'], 1, [
            'type' => 'text_submit', 'component_id' => 'initial_need',
            'value' => 'Necesitamos desarrollar el sitio institucional de una fundación.',
        ])->assertOk()->json();
        $second = $this->action($created['conversation_id'], $created['conversation_token'], $first['version'], [
            'type' => 'single_select', 'component_id' => 'landing_goal', 'value' => 'information',
        ])->assertOk()->json();

        $afterContact = $this->action($created['conversation_id'], $created['conversation_token'], $second['version'], [
            'type' => 'contact_submit', 'component_id' => 'contact',
            'value' => ['name' => 'Luciano', 'channel' => 'email', 'contact_value' => 'luciano@example.com'],
        ])->assertOk()
            ->assertJsonPath('turn.parts.0.id', 'content_readiness')
            ->json();

        $response = $this->action($created['conversation_id'], $created['conversation_token'], $afterContact['version'], [
            'type' => 'single_select', 'component_id' => 'content_readiness', 'value' => 'ready',
        ])->assertOk()
            ->assertJsonCount(3, 'turn.parts.0.items');

        $message = (string) $response->json('turn.message');
        self::assertStringContainsString('Sí, Luciano.', $message);
        self::assertStringContainsString('Fundación Huésped', $message);
        self::assertStringContainsString('Amnistía Internacional', $message);
        self::assertStringContainsString('CEDES', $message);
        self::assertStringContainsString('Hemos realizado proyectos de estas características', $message);
    }

    public function test_generic_web_request_gathers_project_context_before_showing_evidence(): void
    {
        $this->createEvidenceClient('Fundación Huésped', 'Organizaciones sociales', [
            'title' => 'Sitio institucional para organización social',
            'problem' => 'Ordenar contenidos y recibir donaciones.',
            'solution' => 'Diseñamos y desarrollamos una experiencia web administrable.',
            'tags' => 'web, ONG, donaciones',
            'use_authorized' => true,
            'chat_enabled' => true,
        ]);

        $created = $this->postJson('/api/paco/conversations')->assertCreated()->json();
        $first = $this->action($created['conversation_id'], $created['conversation_token'], 1, [
            'type' => 'text_submit', 'component_id' => 'initial_need', 'value' => 'Una web',
        ])->assertOk()->json();
        $second = $this->action($created['conversation_id'], $created['conversation_token'], $first['version'], [
            'type' => 'single_select', 'component_id' => 'landing_goal',
            'value' => ['choice' => 'conversion', 'detail' => 'Aumentar ventas'],
        ])->assertOk()->json();

        $afterContact = $this->action($created['conversation_id'], $created['conversation_token'], $second['version'], [
            'type' => 'contact_submit', 'component_id' => 'contact',
            'value' => ['name' => 'Luciano', 'channel' => 'email', 'contact_value' => 'luciano@example.com'],
        ])->assertOk()
            ->assertJsonPath('stage', 'qualifying')
            ->assertJsonPath('turn.parts.0.id', 'project_context')
            ->assertJsonPath('turn.meta.question_text', '¿Para qué tipo de negocio u organización es la web y qué debería poder hacer?')
            ->json();

        self::assertStringNotContainsString('Fundación Huésped', (string) data_get($afterContact, 'turn.message'));
        self::assertSame(0, ContentImpression::query()->count());

        $withContext = $this->action($created['conversation_id'], $created['conversation_token'], $afterContact['version'], [
            'type' => 'text_submit', 'component_id' => 'project_context',
            'value' => 'Es para una organización social y debe recibir donaciones.',
        ])->assertOk()
            ->assertJsonPath('stage', 'trust_building');

        self::assertSame('content_carousel', $withContext->json('turn.parts.0.type'));
        self::assertStringContainsString('Fundación Huésped', (string) $withContext->json('turn.message'));
    }

    public function test_local_business_website_does_not_ask_for_an_organization_name(): void
    {
        $created = $this->postJson('/api/paco/conversations', [])->assertCreated()->json();

        $response = $this->action($created['conversation_id'], $created['conversation_token'], 1, [
            'type' => 'text_submit',
            'component_id' => 'initial_need',
            'value' => 'Necesito crear una web para una barbería.',
        ])->assertOk();

        $response->assertJsonPath('turn.parts.0.id', 'landing_goal');
        self::assertStringNotContainsString('organización', (string) $response->json('turn.message'));
    }

    public function test_greeting_prompts_for_the_reason_before_advancing_the_playbook(): void
    {
        $created = $this->postJson('/api/paco/conversations', [])->assertCreated()->json();

        $clarification = $this->action($created['conversation_id'], $created['conversation_token'], 1, [
            'type' => 'text_submit',
            'component_id' => 'initial_need',
            'value' => 'Hola',
        ])->assertOk()
            ->assertJsonPath('turn.parts.0.id', 'initial_need')
            ->assertJsonPath('turn.message', 'Hola. ¿Cuál es el motivo de tu consulta? Contanos qué necesitás resolver o qué proyecto tenés en mente.')
            ->json();

        self::assertStringNotContainsString('organización', (string) $clarification['turn']['message']);

        $this->action($created['conversation_id'], $created['conversation_token'], $clarification['version'], [
            'type' => 'text_submit',
            'component_id' => 'initial_need',
            'value' => 'Necesito crear una web para una barbería.',
        ])->assertOk()
            ->assertJsonPath('turn.parts.0.id', 'landing_goal');

        $lead = Lead::query()->firstOrFail();
        self::assertSame('Necesito crear una web para una barbería.', $lead->problem_summary);
        self::assertSame(1, $lead->conversation->useful_interaction_count);
    }

    public function test_a_clear_page_with_customer_support_chat_is_understood_on_the_first_attempt(): void
    {
        Cache::put('paco:opencode-go:circuit-open', ['reason' => 'test'], now()->addMinute());
        $created = $this->postJson('/api/paco/conversations')->assertCreated()->json();

        $response = $this->action($created['conversation_id'], $created['conversation_token'], 1, [
            'type' => 'text_submit',
            'component_id' => 'initial_need',
            'value' => 'Hola me gustaría hacer una página que contenga un chat de ayuda para mis clientes',
        ])->assertOk();

        $response->assertJsonPath('turn.parts.0.id', 'landing_goal');
        self::assertStringNotContainsString('Cuál es el motivo de tu consulta', (string) $response->json('turn.message'));
        self::assertSame(0, Lead::query()->firstOrFail()->state['clarification_attempts'] ?? 0);
    }

    public function test_a_reference_to_the_previous_need_uses_short_term_conversation_memory(): void
    {
        $analysis = app(DeterministicPacoModelGateway::class)->analyze('Ya te dije', null, [
            'previous_need' => 'Quiero hacer una página con un chat para atender clientes.',
        ]);

        self::assertSame('web_institucional', $analysis->primaryIntent);
        self::assertGreaterThan(0.8, $analysis->confidence);
    }

    public function test_model_can_add_a_relevant_question_outside_the_fixed_playbook_order(): void
    {
        Cache::flush();
        config()->set('paco.opencode_go.api_key', 'test-key');
        $this->app->instance(PacoModelGateway::class, app(OpenCodeGoModelGateway::class));
        Http::fake(['https://opencode.ai/*' => Http::response([
            'choices' => [[
                'message' => ['content' => json_encode([
                    'primary_intent' => 'landing_page',
                    'confidence' => 0.91,
                    'facts' => [],
                    'question_priorities' => ['landing_goal', 'current_platform', 'tools_involved'],
                    'acknowledgement' => 'Buscan una página con un chat para atender consultas.',
                ], JSON_UNESCAPED_UNICODE)],
            ]],
        ])]);

        $created = $this->postJson('/api/paco/conversations')->assertCreated()->json();
        $first = $this->action($created['conversation_id'], $created['conversation_token'], 1, [
            'type' => 'text_submit', 'component_id' => 'initial_need',
            'value' => 'Necesitamos una página con un chat para clientes.',
        ])->assertOk()->assertJsonPath('turn.parts.0.id', 'landing_goal')->json();
        $second = $this->action($created['conversation_id'], $created['conversation_token'], $first['version'], [
            'type' => 'single_select', 'component_id' => 'landing_goal', 'value' => 'leads',
        ])->assertOk()->assertJsonPath('turn.parts.0.id', 'contact')->json();

        $this->action($created['conversation_id'], $created['conversation_token'], $second['version'], [
            'type' => 'contact_submit', 'component_id' => 'contact',
            'value' => ['name' => 'Bren', 'channel' => 'email', 'contact_value' => 'bren@example.com'],
        ])->assertOk()
            ->assertJsonPath('turn.parts.0.id', 'current_platform');
    }

    public function test_failed_clarifications_do_not_consume_the_commercial_conversation_limit(): void
    {
        Cache::put('paco:opencode-go:circuit-open', ['reason' => 'test'], now()->addMinute());
        $created = $this->postJson('/api/paco/conversations')->assertCreated()->json();
        $one = $this->action($created['conversation_id'], $created['conversation_token'], 1, [
            'type' => 'text_submit', 'component_id' => 'initial_need', 'value' => 'hola',
        ])->assertOk()->json();
        $two = $this->action($created['conversation_id'], $created['conversation_token'], $one['version'], [
            'type' => 'text_submit', 'component_id' => 'initial_need', 'value' => 'nada',
        ])->assertOk()->json();
        $three = $this->action($created['conversation_id'], $created['conversation_token'], $two['version'], [
            'type' => 'text_submit', 'component_id' => 'initial_need',
            'value' => 'Quiero hacer una página con un chat de ayuda para clientes.',
        ])->assertOk()->json();
        $four = $this->action($created['conversation_id'], $created['conversation_token'], $three['version'], [
            'type' => 'single_select', 'component_id' => 'landing_goal', 'value' => 'leads',
        ])->assertOk()->json();
        $five = $this->action($created['conversation_id'], $created['conversation_token'], $four['version'], [
            'type' => 'contact_submit', 'component_id' => 'contact',
            'value' => ['name' => 'Bren', 'channel' => 'email', 'contact_value' => 'bren@example.com'],
        ])->assertOk()->json();
        $six = $this->action($created['conversation_id'], $created['conversation_token'], $five['version'], [
            'type' => 'single_select', 'component_id' => 'content_readiness', 'value' => 'ready',
        ])->assertOk()->json();

        $response = $this->action($created['conversation_id'], $created['conversation_token'], $six['version'], [
            'type' => 'single_select', 'component_id' => 'contact_context', 'value' => 'owner',
        ])->assertOk();

        self::assertNotSame('close_limit', $response->json('turn.meta.objective'));
        self::assertSame('active', $response->json('status'));
    }

    public function test_professional_website_collects_contact_role_and_organization_structure(): void
    {
        $created = $this->postJson('/api/paco/conversations')->assertCreated()->json();
        $first = $this->action($created['conversation_id'], $created['conversation_token'], 1, [
            'type' => 'text_submit', 'component_id' => 'initial_need',
            'value' => 'Quiero hacer una web para una abogada.',
        ])->assertOk()->assertJsonPath('turn.parts.0.id', 'landing_goal')->json();
        $second = $this->action($created['conversation_id'], $created['conversation_token'], $first['version'], [
            'type' => 'single_select', 'component_id' => 'landing_goal', 'value' => 'information',
        ])->assertOk()->assertJsonPath('turn.parts.0.id', 'contact')->json();
        $third = $this->action($created['conversation_id'], $created['conversation_token'], $second['version'], [
            'type' => 'contact_submit', 'component_id' => 'contact',
            'value' => ['name' => 'Marcela', 'channel' => 'email', 'contact_value' => 'marcela@example.com'],
        ])->assertOk()->assertJsonPath('turn.parts.0.id', 'content_readiness')->json();
        $fourth = $this->action($created['conversation_id'], $created['conversation_token'], $third['version'], [
            'type' => 'single_select', 'component_id' => 'content_readiness', 'value' => 'ready',
        ])->assertOk()->assertJsonPath('turn.parts.0.id', 'contact_context')->json();
        $fifth = $this->action($created['conversation_id'], $created['conversation_token'], $fourth['version'], [
            'type' => 'single_select', 'component_id' => 'contact_context', 'value' => 'assistant',
        ])->assertOk();

        $fifth->assertJsonPath('status', 'active')
            ->assertJsonPath('turn.parts.0.id', 'organization_structure');
    }

    public function test_custom_system_collects_scale_and_budget_before_closing(): void
    {
        Queue::fake();
        $created = $this->postJson('/api/paco/conversations')->assertCreated()->json();
        $first = $this->action($created['conversation_id'], $created['conversation_token'], 1, [
            'type' => 'text_submit', 'component_id' => 'initial_need',
            'value' => 'Tengo un consorcio y queremos desarrollar un sistema de administración.',
        ])->assertOk()->assertJsonPath('turn.parts.0.id', 'current_process')->json();
        $second = $this->action($created['conversation_id'], $created['conversation_token'], $first['version'], [
            'type' => 'text_submit', 'component_id' => 'current_process', 'value' => 'Lo resolvemos con Excel.',
        ])->assertOk()->assertJsonPath('turn.parts.0.id', 'contact')->json();
        $third = $this->action($created['conversation_id'], $created['conversation_token'], $second['version'], [
            'type' => 'contact_submit', 'component_id' => 'contact',
            'value' => ['name' => 'Luciano', 'channel' => 'email', 'contact_value' => 'luciano@example.com'],
        ])->assertOk()->assertJsonPath('turn.parts.0.id', 'main_pain')->json();
        $fourth = $this->action($created['conversation_id'], $created['conversation_token'], $third['version'], [
            'type' => 'text_submit', 'component_id' => 'main_pain',
            'value' => 'Hay mucha carga manual y se duplican datos.',
        ])->assertOk()->assertJsonPath('turn.parts.0.id', 'people_or_volume')->json();
        $fifth = $this->action($created['conversation_id'], $created['conversation_token'], $fourth['version'], [
            'type' => 'text_submit', 'component_id' => 'people_or_volume',
            'value' => 'Son 300 unidades y lo usan cinco administradores.',
        ])->assertOk();

        $fifth->assertJsonPath('status', 'active')
            ->assertJsonPath('turn.parts.0.id', 'budget_context');
    }

    public function test_questions_objections_and_empty_text_do_not_complete_the_active_field(): void
    {
        $created = $this->postJson('/api/paco/conversations')->assertCreated()->json();
        $first = $this->action($created['conversation_id'], $created['conversation_token'], 1, [
            'type' => 'text_submit',
            'component_id' => 'initial_need',
            'value' => 'Quiero desarrollar un sistema de administración para un consorcio.',
        ])->assertOk()
            ->assertJsonPath('turn.parts.0.id', 'current_process')
            ->json();

        $question = $this->action($created['conversation_id'], $created['conversation_token'], $first['version'], [
            'type' => 'text_submit',
            'component_id' => 'current_process',
            'value' => '¿Cuál es la diferencia?',
        ])->assertOk()
            ->assertJsonPath('turn.parts.0.id', 'current_process')
            ->json();

        $objection = $this->action($created['conversation_id'], $created['conversation_token'], $question['version'], [
            'type' => 'text_submit',
            'component_id' => 'current_process',
            'value' => 'No me contestaste',
        ])->assertOk()
            ->assertJsonPath('turn.parts.0.id', 'current_process')
            ->json();

        $empty = $this->action($created['conversation_id'], $created['conversation_token'], $objection['version'], [
            'type' => 'text_submit',
            'component_id' => 'current_process',
            'value' => '???',
        ])->assertOk()
            ->assertJsonPath('turn.parts.0.id', 'current_process');

        $lead = Lead::query()->firstOrFail();
        self::assertArrayNotHasKey('current_process', $lead->state['answered_fields']);
        self::assertSame(1, $lead->conversation->useful_interaction_count);
        self::assertSame(0, $lead->state['questions_after_contact']);
        self::assertSame(3, $lead->conversation->modelRuns()->where('phase', 'turn_router')->count());
    }

    public function test_conversation_requires_the_opaque_token(): void
    {
        $created = $this->postJson('/api/paco/conversations', [])->assertCreated()->json();

        $this->getJson("/api/paco/conversations/{$created['conversation_id']}")
            ->assertForbidden();
    }

    public function test_action_is_idempotent(): void
    {
        $created = $this->postJson('/api/paco/conversations', [])->assertCreated()->json();
        $key = (string) Str::uuid();
        $payload = [
            'conversation_version' => 1,
            'action' => [
                'type' => 'text_submit',
                'component_id' => 'initial_need',
                'value' => 'Necesito automatizar un proceso manual.',
            ],
        ];

        $headers = ['Authorization' => "Bearer {$created['conversation_token']}", 'Idempotency-Key' => $key];
        $first = $this->withHeaders($headers)->postJson(
            "/api/paco/conversations/{$created['conversation_id']}/actions",
            $payload,
        )->assertOk()->json();
        $second = $this->withHeaders($headers)->postJson(
            "/api/paco/conversations/{$created['conversation_id']}/actions",
            $payload,
        )->assertOk()->json();

        self::assertSame($first['version'], $second['version']);
        self::assertCount(3, $second['turns']);
    }

    public function test_prefill_is_consumed_and_returned_for_confirmation(): void
    {
        $token = Str::random(48);
        Cache::put("paco:prefill:{$token}", [
            'campaign' => 'home_default',
            'name' => 'Ana',
            'email' => 'ana@example.com',
            'contact_channel' => 'email',
            'initial_query' => 'Necesitamos una landing.',
        ], now()->addHour());

        $this->postJson('/api/paco/conversations', [
            'campaign' => 'home_default',
            'prefill_token' => $token,
        ])->assertCreated()
            ->assertJsonPath('prefill.name', 'Ana')
            ->assertJsonPath('prefill.initial_query', 'Necesitamos una landing.')
            ->assertJsonPath('prefill.requires_confirmation', true);

        self::assertNull(Cache::get("paco:prefill:{$token}"));
    }

    public function test_prefill_service_creates_a_private_one_use_link(): void
    {
        $campaign = Campaign::query()->where('code', 'direct_default')->firstOrFail();
        $link = app(PacoPrefillService::class)->createLink($campaign, [
            'name' => 'Ana',
            'email' => 'ana@example.com',
            'contact_channel' => 'email',
            'initial_query' => 'Necesitamos automatizar una aprobación.',
        ], [
            'utm_source' => 'newsletter',
            'utm_medium' => 'email',
        ]);
        parse_str((string) parse_url($link, PHP_URL_QUERY), $query);

        self::assertSame('direct_default', $query['campaign']);
        self::assertSame('newsletter', $query['utm_source']);
        self::assertNotEmpty($query['prefill_token']);
        self::assertIsArray(Cache::get('paco:prefill:'.$query['prefill_token']));
    }

    public function test_campaign_rejects_an_origin_outside_its_allowlist(): void
    {
        $this->postJson('/api/paco/conversations', [
            'campaign' => 'home_default',
            'origin_url' => 'https://example.org/campaign',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('campaign');
    }

    public function test_lead_notification_job_sends_the_internal_email(): void
    {
        Mail::fake();
        config()->set('paco.lead_notification_to', 'equipo@example.com');
        $this->postJson('/api/paco/conversations')->assertCreated();
        $lead = Lead::query()->firstOrFail();

        (new SendLeadNotification($lead->id))->handle();

        Mail::assertSent(PacoLeadReceived::class, fn (PacoLeadReceived $mail): bool => $mail->hasTo('equipo@example.com'));
    }

    public function test_opencode_usage_limit_opens_circuit_and_uses_deterministic_gateway(): void
    {
        Cache::flush();
        config()->set('paco.opencode_go.api_key', 'test-key');
        Http::fake(['https://opencode.ai/*' => Http::response(['error' => ['message' => 'rate limit exceeded']], 429)]);

        $gateway = app(OpenCodeGoModelGateway::class);
        $result = $gateway->analyze('Necesitamos una landing para una campaña.');

        self::assertSame('landing_page', $result->primaryIntent);
        self::assertTrue($gateway->usedFallback());
        self::assertSame('application-fallback', $gateway->provider());
        self::assertTrue(Cache::has('paco:opencode-go:circuit-open'));
    }

    public function test_opencode_structured_output_is_used_when_available(): void
    {
        Cache::flush();
        config()->set('paco.opencode_go.api_key', 'test-key');
        Http::fake(['https://opencode.ai/*' => Http::response([
            'choices' => [[
                'message' => ['content' => '{"primary_intent":"landing_page","confidence":0.88,"facts":[],"question_priorities":["contact_context","landing_goal"],"acknowledgement":"Vemos que necesitan una landing para la campaña."}'],
            ]],
        ])]);

        $gateway = app(OpenCodeGoModelGateway::class);
        $result = $gateway->analyze('Necesitamos una landing.');

        self::assertSame('landing_page', $result->primaryIntent);
        self::assertSame(0.88, $result->confidence);
        self::assertSame(['contact_context', 'landing_goal'], $result->questionPriorities);
        self::assertSame('Vemos que necesitan una landing para la campaña.', $result->acknowledgement);
        self::assertFalse($gateway->usedFallback());
        self::assertSame('opencode-go', $gateway->provider());
        Http::assertSent(fn ($request): bool => $request['model'] === config('paco.opencode_go.model')
            && $request['max_tokens'] === 1600);
    }

    public function test_opencode_turn_interpreter_generates_a_reply_without_accepting_the_field(): void
    {
        Cache::flush();
        config()->set('paco.opencode_go.api_key', 'test-key');
        Http::fake(['https://opencode.ai/*' => Http::response([
            'choices' => [[
                'message' => ['content' => json_encode([
                    'disposition' => 'question',
                    'answers_current_question' => false,
                    'useful' => false,
                    'confidence' => 0.94,
                    'normalized_answer' => null,
                    'reply' => 'Una tienda online prioriza un catálogo y la compra; una plataforma puede incluir procesos más amplios.',
                ], JSON_UNESCAPED_UNICODE)],
            ]],
        ])]);

        $gateway = app(OpenCodeGoModelGateway::class);
        $result = $gateway->interpretTurn('¿Cuál es la diferencia?', [
            'active_question' => ['prompt' => '¿Cómo resuelven hoy ese proceso?'],
            'last_assistant_message' => 'Nos referimos a una tienda online o plataforma de venta.',
        ]);

        self::assertSame('question', $result->disposition);
        self::assertFalse($result->answersCurrentQuestion);
        self::assertFalse($result->useful);
        self::assertStringContainsString('tienda online', (string) $result->reply);
        self::assertFalse($gateway->usedFallback());
    }

    public function test_opencode_evidence_planner_can_choose_relevant_authorized_items_from_conversation_context(): void
    {
        Cache::flush();
        config()->set('paco.opencode_go.api_key', 'test-key');
        Http::fake(['https://opencode.ai/*' => Http::response([
            'choices' => [[
                'message' => ['content' => json_encode([
                    'selected_item_ids' => ['work-huesped', 'testimonial-huesped'],
                    'relationship' => 'same_problem_same_industry',
                    'acknowledgement' => 'Sí, Luciano: buscan una web institucional clara.',
                    'testimonial_item_id' => 'testimonial-huesped',
                ], JSON_UNESCAPED_UNICODE)],
            ]],
        ])]);

        $gateway = app(OpenCodeGoModelGateway::class);
        $plan = $gateway->planEvidence([
            'visitor_name' => 'Luciano',
            'conversation' => [
                ['speaker' => 'visitor', 'message' => 'Necesitamos una web para una fundación.'],
                ['speaker' => 'visitor', 'message' => 'Queremos ordenar mejor los contenidos.'],
            ],
        ], [
            ['item_id' => 'work-huesped', 'entity_type' => 'work', 'client_name' => 'Fundación Huésped'],
            ['item_id' => 'testimonial-huesped', 'entity_type' => 'testimonial', 'client_name' => 'Fundación Huésped'],
            ['item_id' => 'work-unrelated', 'entity_type' => 'work', 'client_name' => 'Otro cliente'],
        ]);

        self::assertSame(['work-huesped', 'testimonial-huesped'], $plan->selectedItemIds);
        self::assertSame('testimonial-huesped', $plan->testimonialItemId);
        self::assertSame('same_problem_same_industry', $plan->relationship);
        self::assertStringContainsString('Luciano', (string) $plan->acknowledgement);
        self::assertFalse($gateway->usedFallback());
        Http::assertSent(fn ($request): bool => str_contains((string) $request['messages'][0]['content'], 'estratega comercial')
            && str_contains((string) $request['messages'][1]['content'], 'Queremos ordenar mejor los contenidos.'));
    }

    public function test_turn_interpreter_accepts_a_short_but_meaningful_answer(): void
    {
        Cache::flush();
        config()->set('paco.opencode_go.api_key', 'test-key');
        Http::fake(['https://opencode.ai/*' => Http::response([
            'choices' => [[
                'message' => ['content' => json_encode([
                    'disposition' => 'low_information',
                    'answers_current_question' => false,
                    'useful' => false,
                    'confidence' => 0.85,
                    'normalized_answer' => null,
                    'reply' => null,
                ])],
            ]],
        ])]);

        $result = app(OpenCodeGoModelGateway::class)->interpretTurn('Vendemos físico', [
            'active_question' => ['code' => 'current_process', 'prompt' => '¿Cómo resuelven hoy ese proceso?'],
        ]);

        self::assertTrue($result->answersCurrentQuestion);
        self::assertTrue($result->useful);
        self::assertSame('Vendemos físico', $result->normalizedAnswer);
    }

    /** @param array<string, mixed> $action */
    private function action(string $id, string $token, int $version, array $action)
    {
        return $this->withHeaders([
            'Authorization' => "Bearer {$token}",
            'Idempotency-Key' => (string) Str::uuid(),
        ])->postJson("/api/paco/conversations/{$id}/actions", [
            'conversation_version' => $version,
            'action' => $action,
        ]);
    }

    /** @param array<string, mixed> $work */
    private function createEvidenceClient(string $name, string $industry, array $work): Client
    {
        $client = Client::query()->create([
            'public_name' => $name,
            'industry' => $industry,
            'paco_use_authorized' => true,
            'paco_chat_enabled' => true,
            'blocks' => [],
            'works' => [$work],
            'testimonials' => [],
            'preview_items' => [],
        ]);
        Route::query()->create([
            'title' => $name,
            'slug' => Str::slug($name),
            'full_slug' => 'cliente/'.Str::slug($name),
            'status' => Status::Published,
            'routable_type' => Client::class,
            'routable_id' => $client->id,
        ]);

        return $client;
    }
}
