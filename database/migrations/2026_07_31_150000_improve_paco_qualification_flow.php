<?php

declare(strict_types=1);

use App\Models\Paco\Playbook;
use App\Models\Paco\Question;
use App\Models\Paco\ResponseBlock;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    private const CONTACT_COPY = 'Ya tenemos un buen punto de partida. Para continuar y poder responderte, compartinos tus datos de contacto.';

    public function up(): void
    {
        $this->updateLandingGoal();
        $this->createContextQuestions();

        Question::query()->where('code', 'contact')->first()?->forceFill([
            'prompt' => self::CONTACT_COPY,
            'version' => 2,
        ])->save();
        ResponseBlock::query()->where('code', 'contact_transition_default')->first()?->forceFill([
            'text' => self::CONTACT_COPY,
            'version' => 3,
        ])->save();

        $this->configurePlaybook('general_discovery', 2, 7, [
            ['organization_name', 'high', 30],
            ['contact_context', 'high', 40],
            ['decision_role', 'medium', 50],
        ]);
        $this->configurePlaybook('landing_project', 3, 9, [
            ['landing_goal', 'high', 30],
            ['content_readiness', 'high', 40],
            ['contact_context', 'high', 50],
            ['organization_structure', 'high', 60],
            ['design_readiness', 'medium', 70],
            ['target_date', 'medium', 80],
            ['decision_role', 'medium', 90],
            ['maintenance_need', 'low', 100],
        ]);
        $this->configurePlaybook('custom_system', 3, 9, [
            ['current_process', 'high', 30],
            ['main_pain', 'high', 40],
            ['people_or_volume', 'high', 50],
            ['budget_context', 'medium', 60],
            ['tools_involved', 'medium', 70],
            ['decision_role', 'medium', 80],
            ['target_date', 'medium', 90],
        ]);
    }

    public function down(): void
    {
        $landing = Question::query()->where('code', 'landing_goal')->first();
        $landing?->forceFill([
            'options' => [
                ['value' => 'campaign', 'label' => 'Una campaña puntual'],
                ['value' => 'leads', 'label' => 'Recibir consultas'],
                ['value' => 'information', 'label' => 'Presentar información'],
                ['value' => 'conversion', 'label' => 'Lograr una acción concreta'],
            ],
            'version' => 1,
        ])->save();

        foreach (['contact_context', 'organization_structure', 'budget_context'] as $code) {
            $question = Question::query()->where('code', $code)->first();
            $question?->playbookFields()->delete();
            $question?->delete();
        }

        Question::query()->where('code', 'contact')->first()?->forceFill([
            'prompt' => 'Genial, te molestamos con algunos datos para entenderlo mejor.',
            'version' => 1,
        ])->save();
        ResponseBlock::query()->where('code', 'contact_transition_default')->first()?->forceFill([
            'text' => 'Genial, te molestamos con algunos datos para entenderlo mejor.',
            'version' => 2,
        ])->save();

        $this->restorePlaybook('general_discovery', 2, 7);
        $this->restorePlaybook('landing_project', 2, 7);
        $this->restorePlaybook('custom_system', 3, 8);
    }

    private function updateLandingGoal(): void
    {
        Question::query()->where('code', 'landing_goal')->first()?->forceFill([
            'options' => [
                $this->detailOption('campaign', 'Una campaña puntual', '¿Qué debería lograr la campaña?'),
                $this->detailOption('leads', 'Recibir consultas', '¿Qué tipo de consultas quieren recibir?'),
                $this->detailOption('information', 'Presentar información', '¿Qué información necesitan destacar?'),
                $this->detailOption('conversion', 'Lograr una acción concreta', '¿Qué acción esperan que realicen las personas?'),
                $this->detailOption('other', 'Otro objetivo', 'Contanos cuál es el objetivo', true),
            ],
            'version' => 2,
        ])->save();
    }

    private function createContextQuestions(): void
    {
        Question::query()->firstOrCreate(['code' => 'contact_context'], [
            'field_code' => 'contact_context',
            'prompt' => '¿Cuál es tu relación con este proyecto?',
            'component_type' => 'single_select',
            'options' => [
                ['value' => 'owner', 'label' => 'Soy la persona responsable'],
                ['value' => 'team_member', 'label' => 'Formo parte del equipo'],
                ['value' => 'assistant', 'label' => 'Estoy ayudando a otra persona'],
                ['value' => 'advisor', 'label' => 'Estoy asesorando o relevando opciones'],
                $this->detailOption('other', 'Otra relación', 'Contanos brevemente cuál es tu relación', true),
            ],
            'is_sensitive' => false,
            'is_skippable' => false,
            'status' => 'active',
            'version' => 1,
        ]);
        Question::query()->firstOrCreate(['code' => 'organization_structure'], [
            'field_code' => 'organization_structure',
            'prompt' => '¿El proyecto es para una persona, un equipo o una organización?',
            'component_type' => 'single_select',
            'options' => [
                ['value' => 'independent', 'label' => 'Una persona o profesional independiente'],
                ['value' => 'team', 'label' => 'Un estudio o equipo'],
                ['value' => 'organization', 'label' => 'Una empresa u organización'],
                $this->detailOption('other', 'Otro caso', 'Contanos brevemente para quién es el proyecto', true),
            ],
            'is_sensitive' => false,
            'is_skippable' => false,
            'status' => 'active',
            'version' => 1,
        ]);
        Question::query()->firstOrCreate(['code' => 'budget_context'], [
            'field_code' => 'budget_context',
            'prompt' => '¿Tienen un rango de inversión previsto para este proyecto?',
            'component_type' => 'single_select',
            'options' => [
                $this->detailOption('defined', 'Sí, tenemos un rango', 'Indicá el rango y la moneda', true),
                ['value' => 'evaluating', 'label' => 'Lo estamos evaluando'],
                ['value' => 'not_defined', 'label' => 'Todavía no lo definimos'],
                ['value' => 'skip', 'label' => 'Prefiero no responder'],
            ],
            'is_sensitive' => true,
            'is_skippable' => true,
            'status' => 'active',
            'version' => 1,
        ]);
    }

    /** @param array<int, array{0: string, 1: string, 2: int}> $fields */
    private function configurePlaybook(
        string $code,
        int $minimumAfterContact,
        int $maxInteractions,
        array $fields,
    ): void {
        $playbook = Playbook::query()->where('code', $code)->first();
        if (! $playbook) {
            return;
        }

        $settings = $playbook->settings ?? [];
        $settings['minimum_questions_after_contact'] = $minimumAfterContact;
        $playbook->forceFill([
            'max_questions_after_contact' => $minimumAfterContact,
            'max_interactions' => $maxInteractions,
            'settings' => $settings,
            'version' => $playbook->version + 1,
        ])->save();

        foreach ($fields as [$questionCode, $importance, $priority]) {
            $question = Question::query()->where('code', $questionCode)->firstOrFail();
            $playbook->fields()->updateOrCreate(
                ['field_code' => $question->field_code],
                ['question_id' => $question->id, 'importance' => $importance, 'priority' => $priority],
            );
        }
    }

    private function restorePlaybook(string $code, int $maxAfterContact, int $maxInteractions): void
    {
        $playbook = Playbook::query()->where('code', $code)->first();
        if (! $playbook) {
            return;
        }

        $settings = $playbook->settings ?? [];
        unset($settings['minimum_questions_after_contact']);
        $playbook->forceFill([
            'max_questions_after_contact' => $maxAfterContact,
            'max_interactions' => $maxInteractions,
            'settings' => $settings,
        ])->save();
    }

    /** @return array<string, mixed> */
    private function detailOption(string $value, string $label, string $detailLabel, bool $required = false): array
    {
        return [
            'value' => $value,
            'label' => $label,
            'allow_detail' => true,
            'detail_label' => $detailLabel,
            'detail_placeholder' => 'Escribí una aclaración breve',
            'detail_required' => $required,
            'detail_max_length' => 600,
        ];
    }
};
