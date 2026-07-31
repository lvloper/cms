<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Paco\Question;
use Illuminate\Database\Seeder;

final class PacoQuestionSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->questions() as $question) {
            Question::query()->firstOrCreate(['code' => $question['code']], $question);
        }
    }

    /** @return array<int, array<string, mixed>> */
    private function questions(): array
    {
        return [
            $this->text('initial_need', 'problem_summary', '¿En qué podemos ayudarte?', false),
            [
                'code' => 'contact',
                'field_code' => 'contact',
                'prompt' => 'Ya tenemos un buen punto de partida. Para continuar y poder responderte, compartinos tus datos de contacto.',
                'component_type' => 'contact_form',
                'is_sensitive' => true,
                'is_skippable' => false,
                'validation_schema' => ['name' => 'required', 'channel' => 'required', 'contact_value' => 'required'],
                'status' => 'active',
                'version' => 1,
            ],
            $this->text('organization_name', 'organization_name', '¿Cómo se llama la organización?', true),
            $this->select('decision_role', 'decision_role', '¿Qué rol tenés en esta decisión?', [
                ['value' => 'decision_maker', 'label' => 'La decisión depende de mí'],
                ['value' => 'shared_decision', 'label' => 'La evaluamos en equipo'],
                ['value' => 'researcher', 'label' => 'Estoy relevando opciones'],
                ['value' => 'early_research', 'label' => 'Por ahora solo estoy averiguando'],
                ['value' => 'skip', 'label' => 'Prefiero no responder'],
            ], true, true),
            $this->date('target_date', 'deadline', '¿Tienen una fecha prevista?', true),
            $this->select('relevant_scale', 'relevant_scale', '¿Qué escala tiene hoy la operación?', [
                ['value' => 'small', 'label' => 'Pequeña'],
                ['value' => 'medium', 'label' => 'Media'],
                ['value' => 'large', 'label' => 'Grande'],
                ['value' => 'unknown', 'label' => 'Todavía no lo sabemos'],
            ], true),
            $this->select('landing_goal', 'landing_goal', '¿Cuál es el objetivo principal?', [
                $this->optionWithDetail('campaign', 'Una campaña puntual', '¿Qué debería lograr la campaña?'),
                $this->optionWithDetail('leads', 'Recibir consultas', '¿Qué tipo de consultas quieren recibir?'),
                $this->optionWithDetail('information', 'Presentar información', '¿Qué información necesitan destacar?'),
                $this->optionWithDetail('conversion', 'Lograr una acción concreta', '¿Qué acción esperan que realicen las personas?'),
                $this->optionWithDetail('other', 'Otro objetivo', 'Contanos cuál es el objetivo', true),
            ]),
            $this->text(
                'project_context',
                'project_context',
                '¿Para qué tipo de negocio u organización es la web y qué debería poder hacer?',
                false,
            ),
            $this->select('content_readiness', 'content_readiness', '¿Ya tienen definidos los contenidos?', [
                ['value' => 'ready', 'label' => 'Sí, ya están definidos'],
                [
                    'value' => 'partial',
                    'label' => 'Tenemos una parte',
                    'allow_detail' => true,
                    'detail_label' => '¿Qué tienen preparado y qué les falta?',
                    'detail_placeholder' => 'Por ejemplo: tenemos los textos, pero nos faltan las fotos',
                ],
                [
                    'value' => 'need_help',
                    'label' => 'Necesitamos ayuda con eso',
                    'allow_detail' => true,
                    'detail_label' => '¿Con qué contenidos necesitan ayuda?',
                    'detail_placeholder' => 'Contanos brevemente qué necesitan resolver',
                ],
            ]),
            $this->select('design_readiness', 'design_readiness', '¿Ya cuentan con un diseño?', [
                ['value' => 'ready', 'label' => 'Sí'],
                ['value' => 'partial', 'label' => 'Hay una base'],
                ['value' => 'not_ready', 'label' => 'No todavía'],
            ], true),
            $this->select('maintenance_need', 'maintenance_need', '¿Necesitan acompañamiento después de publicar?', [
                ['value' => 'yes', 'label' => 'Sí'],
                ['value' => 'no', 'label' => 'No'],
                ['value' => 'unknown', 'label' => 'Todavía no lo sabemos'],
            ], true),
            $this->select('contact_context', 'contact_context', '¿Cuál es tu relación con este proyecto?', [
                ['value' => 'owner', 'label' => 'Soy la persona responsable'],
                ['value' => 'team_member', 'label' => 'Formo parte del equipo'],
                ['value' => 'assistant', 'label' => 'Estoy ayudando a otra persona'],
                ['value' => 'advisor', 'label' => 'Estoy asesorando o relevando opciones'],
                $this->optionWithDetail('other', 'Otra relación', 'Contanos brevemente cuál es tu relación', true),
            ]),
            $this->select('organization_structure', 'organization_structure', '¿El proyecto es para una persona, un equipo o una organización?', [
                ['value' => 'independent', 'label' => 'Una persona o profesional independiente'],
                ['value' => 'team', 'label' => 'Un estudio o equipo'],
                ['value' => 'organization', 'label' => 'Una empresa u organización'],
                $this->optionWithDetail('other', 'Otro caso', 'Contanos brevemente para quién es el proyecto', true),
            ]),
            $this->select('budget_context', 'budget_context', '¿Tienen un rango de inversión previsto para este proyecto?', [
                $this->optionWithDetail('defined', 'Sí, tenemos un rango', 'Indicá el rango y la moneda', true),
                ['value' => 'evaluating', 'label' => 'Lo estamos evaluando'],
                ['value' => 'not_defined', 'label' => 'Todavía no lo definimos'],
                ['value' => 'skip', 'label' => 'Prefiero no responder'],
            ], true, true),
            $this->text('current_process', 'current_process', '¿Cómo resuelven hoy ese proceso?', false),
            $this->text('main_pain', 'main_pain', '¿Cuál es el principal problema de la forma actual?', false),
            $this->text('tools_involved', 'tools_involved', '¿Qué herramientas están involucradas?', true),
            $this->text('people_or_volume', 'relevant_scale', '¿Cuántas personas o movimientos intervienen aproximadamente?', true),
            $this->text('current_platform', 'current_platform', '¿Sobre qué plataforma o sistema trabajan hoy?', false),
            $this->select('recurrence_frequency', 'recurrence_frequency', '¿Con qué frecuencia necesitan este trabajo?', [
                ['value' => 'weekly', 'label' => 'Todas las semanas'],
                ['value' => 'monthly', 'label' => 'Todos los meses'],
                ['value' => 'occasional', 'label' => 'De forma ocasional'],
                ['value' => 'unknown', 'label' => 'Todavía no lo sabemos'],
            ], true),
            $this->select('internal_team', 'internal_team', '¿Cuentan con un equipo técnico interno?', [
                ['value' => 'yes', 'label' => 'Sí'],
                ['value' => 'partial', 'label' => 'Parcialmente'],
                ['value' => 'no', 'label' => 'No'],
            ], true),
            [
                'code' => 'work_type',
                'field_code' => 'work_type',
                'prompt' => '¿Qué tipo de acompañamiento necesitan?',
                'component_type' => 'multi_select',
                'options' => [
                    ['value' => 'development', 'label' => 'Desarrollo'],
                    ['value' => 'maintenance', 'label' => 'Mantenimiento'],
                    ['value' => 'automation', 'label' => 'Automatizaciones'],
                    ['value' => 'technical_support', 'label' => 'Capacidad técnica'],
                ],
                'is_sensitive' => false,
                'is_skippable' => true,
                'status' => 'active',
                'version' => 1,
            ],
        ];
    }

    /** @return array<string, mixed> */
    private function text(string $code, string $field, string $prompt, bool $skippable): array
    {
        return [
            'code' => $code,
            'field_code' => $field,
            'prompt' => $prompt,
            'component_type' => 'text_input',
            'is_sensitive' => false,
            'is_skippable' => $skippable,
            'validation_schema' => ['max_length' => 1500],
            'status' => 'active',
            'version' => 1,
        ];
    }

    /** @param array<int, array{value: string, label: string}> $options
     * @return array<string, mixed>
     */
    private function select(
        string $code,
        string $field,
        string $prompt,
        array $options,
        bool $skippable = false,
        bool $sensitive = false,
    ): array {
        return [
            'code' => $code,
            'field_code' => $field,
            'prompt' => $prompt,
            'component_type' => 'single_select',
            'options' => $options,
            'is_sensitive' => $sensitive,
            'is_skippable' => $skippable,
            'status' => 'active',
            'version' => 1,
        ];
    }

    /** @return array<string, mixed> */
    private function date(string $code, string $field, string $prompt, bool $skippable): array
    {
        return [
            'code' => $code,
            'field_code' => $field,
            'prompt' => $prompt,
            'component_type' => 'date',
            'is_sensitive' => false,
            'is_skippable' => $skippable,
            'status' => 'active',
            'version' => 1,
        ];
    }

    /** @return array<string, mixed> */
    private function optionWithDetail(string $value, string $label, string $detailLabel, bool $required = false): array
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
}
