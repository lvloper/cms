<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Paco\Intent;
use App\Models\Paco\Playbook;
use App\Models\Paco\Question;
use Illuminate\Database\Seeder;

final class PacoPlaybookSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->playbooks() as $definition) {
            $playbook = Playbook::query()->firstOrCreate(
                ['code' => $definition['code']],
                [
                    'name' => $definition['name'],
                    'objective' => $definition['objective'],
                    'status' => 'active',
                    'max_interactions' => $definition['max_interactions'],
                    'max_questions_after_contact' => $definition['max_questions_after_contact'],
                    'minimum_sufficiency_score' => 0.700,
                    'settings' => [
                        'seeded' => true,
                        'minimum_questions_after_contact' => $definition['minimum_questions_after_contact']
                            ?? min(2, $definition['max_questions_after_contact']),
                    ],
                    'version' => 1,
                ],
            );

            $intentIds = Intent::query()
                ->whereIn('code', $definition['intents'])
                ->pluck('id')
                ->mapWithKeys(fn (int $id): array => [$id => ['priority' => 100]])
                ->all();

            $playbook->intents()->syncWithoutDetaching($intentIds);

            foreach ($definition['fields'] as $priority => $field) {
                $question = Question::query()->where('code', $field[0])->firstOrFail();
                $playbook->fields()->firstOrCreate(
                    ['field_code' => $question->field_code],
                    [
                        'question_id' => $question->id,
                        'importance' => $field[1],
                        'priority' => ($priority + 1) * 10,
                    ],
                );
            }
        }
    }

    /** @return array<int, array<string, mixed>> */
    private function playbooks(): array
    {
        return [
            [
                'code' => 'general_discovery',
                'name' => 'Descubrimiento general',
                'objective' => 'Entender una consulta todavía no clasificada y obtener contacto.',
                'intents' => ['general', 'consultoria', 'pack'],
                'fields' => [
                    ['initial_need', 'required'], ['contact', 'required'], ['organization_name', 'high'],
                    ['contact_context', 'high'], ['decision_role', 'medium'],
                ],
                'max_interactions' => 7,
                'max_questions_after_contact' => 2,
                'minimum_questions_after_contact' => 2,
            ],
            [
                'code' => 'landing_project',
                'name' => 'Landing o sitio',
                'objective' => 'Entender objetivo, contenidos y contexto de una landing o sitio.',
                'intents' => ['landing_page', 'web_institucional'],
                'fields' => [
                    ['initial_need', 'required'], ['contact', 'required'], ['landing_goal', 'high'],
                    ['project_context', 'medium'],
                    ['content_readiness', 'high'], ['contact_context', 'high'], ['organization_structure', 'high'],
                    ['design_readiness', 'medium'], ['target_date', 'medium'], ['decision_role', 'medium'],
                    ['maintenance_need', 'low'],
                ],
                'max_interactions' => 9,
                'max_questions_after_contact' => 3,
                'minimum_questions_after_contact' => 3,
            ],
            [
                'code' => 'custom_system',
                'name' => 'Sistema a medida',
                'objective' => 'Entender proceso, problema y escala de un sistema a medida.',
                'intents' => ['plataforma_a_medida'],
                'fields' => [
                    ['initial_need', 'required'], ['contact', 'required'], ['current_process', 'high'],
                    ['main_pain', 'high'], ['people_or_volume', 'high'], ['budget_context', 'medium'],
                    ['tools_involved', 'medium'], ['decision_role', 'medium'], ['target_date', 'medium'],
                ],
                'max_interactions' => 9,
                'max_questions_after_contact' => 3,
                'minimum_questions_after_contact' => 3,
            ],
            [
                'code' => 'automation_integration',
                'name' => 'Automatización e integración',
                'objective' => 'Entender el proceso actual, fricción y herramientas involucradas.',
                'intents' => ['automatizacion', 'integracion'],
                'fields' => [
                    ['initial_need', 'required'], ['contact', 'required'], ['current_process', 'high'],
                    ['main_pain', 'high'], ['tools_involved', 'high'], ['people_or_volume', 'medium'],
                    ['decision_role', 'medium'],
                ],
                'max_interactions' => 8,
                'max_questions_after_contact' => 3,
                'minimum_questions_after_contact' => 3,
            ],
            [
                'code' => 'maintenance_monthly',
                'name' => 'Mantenimiento o servicio mensual',
                'objective' => 'Entender la plataforma actual y el acompañamiento recurrente necesario.',
                'intents' => ['mantenimiento', 'servicio_mensual'],
                'fields' => [
                    ['initial_need', 'required'], ['contact', 'required'], ['current_platform', 'high'],
                    ['work_type', 'high'], ['recurrence_frequency', 'medium'], ['internal_team', 'medium'],
                    ['decision_role', 'medium'],
                ],
                'max_interactions' => 8,
                'max_questions_after_contact' => 3,
                'minimum_questions_after_contact' => 2,
            ],
            [
                'code' => 'partnership',
                'name' => 'Socios y agencias',
                'objective' => 'Entender qué capacidad técnica necesita un potencial socio.',
                'intents' => ['partnership'],
                'fields' => [
                    ['initial_need', 'required'], ['contact', 'required'], ['organization_name', 'high'],
                    ['work_type', 'high'], ['recurrence_frequency', 'medium'], ['internal_team', 'medium'],
                    ['decision_role', 'medium'],
                ],
                'max_interactions' => 7,
                'max_questions_after_contact' => 2,
                'minimum_questions_after_contact' => 2,
            ],
            [
                'code' => 'non_commercial',
                'name' => 'Consulta no comercial',
                'objective' => 'Responder o derivar consultas no comerciales sin prolongar el flujo.',
                'intents' => ['job', 'vendor', 'support_existing_client'],
                'fields' => [['initial_need', 'required']],
                'max_interactions' => 3,
                'max_questions_after_contact' => 0,
                'minimum_questions_after_contact' => 0,
            ],
        ];
    }
}
