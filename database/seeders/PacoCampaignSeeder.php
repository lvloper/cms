<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Paco\Campaign;
use App\Models\Paco\Intent;
use App\Models\Paco\Playbook;
use Illuminate\Database\Seeder;

final class PacoCampaignSeeder extends Seeder
{
    public function run(): void
    {
        $host = parse_url((string) config('app.url'), PHP_URL_HOST) ?: 'localhost';

        foreach ($this->campaigns() as $definition) {
            $playbook = Playbook::query()->where('code', $definition['playbook'])->firstOrFail();
            $intent = isset($definition['intent'])
                ? Intent::query()->where('code', $definition['intent'])->firstOrFail()
                : null;

            Campaign::query()->firstOrCreate(
                ['code' => $definition['code']],
                [
                    'name' => $definition['name'],
                    'status' => $definition['status'],
                    'initial_message' => $definition['message'],
                    'context' => ['seeded' => true],
                    'preferred_playbook_id' => $playbook->id,
                    'preferred_intent_id' => $intent?->id,
                    'max_interactions' => $definition['max_interactions'],
                    'allowed_origins' => [$host, 'localhost', '127.0.0.1'],
                ],
            );
        }
    }

    /** @return array<int, array<string, mixed>> */
    private function campaigns(): array
    {
        return [
            [
                'code' => 'home_default',
                'name' => 'Home de Socies',
                'status' => 'active',
                'message' => 'Hola, ¿En qué podemos ayudarte?',
                'playbook' => 'general_discovery',
                'max_interactions' => 7,
            ],
            [
                'code' => 'direct_default',
                'name' => 'Enlace directo',
                'status' => 'active',
                'message' => 'Hola, ¿En qué podemos ayudarte?',
                'playbook' => 'general_discovery',
                'max_interactions' => 7,
            ],
            [
                'code' => 'landing_services',
                'name' => 'Landing y sitios',
                'status' => 'draft',
                'message' => 'Contanos qué necesitan comunicar o lograr con el sitio.',
                'playbook' => 'landing_project',
                'intent' => 'landing_page',
                'max_interactions' => 7,
            ],
            [
                'code' => 'automation_services',
                'name' => 'Automatización e integraciones',
                'status' => 'draft',
                'message' => 'Contanos qué proceso quieren simplificar o conectar.',
                'playbook' => 'automation_integration',
                'intent' => 'automatizacion',
                'max_interactions' => 8,
            ],
            [
                'code' => 'monthly_services',
                'name' => 'Evolución y capacidad mensual',
                'status' => 'draft',
                'message' => 'Contanos qué plataforma o necesidad necesita continuidad.',
                'playbook' => 'maintenance_monthly',
                'intent' => 'servicio_mensual',
                'max_interactions' => 8,
            ],
        ];
    }
}
