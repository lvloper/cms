<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Paco\Intent;
use App\Models\Paco\ResponseBlock;
use App\Models\Paco\ServiceFitRule;
use Illuminate\Database\Seeder;

final class PacoServiceFitRuleSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            'landing_page' => 'supported',
            'web_institucional' => 'supported',
            'plataforma_a_medida' => 'supported',
            'automatizacion' => 'supported',
            'integracion' => 'supported',
            'mantenimiento' => 'supported',
            'servicio_mensual' => 'supported',
            'partnership' => 'supported',
            'consultoria' => 'conditional',
            'pack' => 'conditional',
            'support_existing_client' => 'conditional',
            'job' => 'unsupported',
            'vendor' => 'unsupported',
            'general' => 'unknown',
        ];

        $unsupportedBlock = ResponseBlock::query()->where('code', 'unsupported_default')->firstOrFail();

        foreach ($statuses as $intentCode => $status) {
            $intent = Intent::query()->where('code', $intentCode)->firstOrFail();
            ServiceFitRule::query()->firstOrCreate(
                ['code' => "fit_{$intentCode}"],
                [
                    'intent_id' => $intent->id,
                    'status' => $status,
                    'approved_response_block_id' => $status === 'unsupported' ? $unsupportedBlock->id : null,
                    'priority' => 100,
                    'version' => 1,
                    'active' => true,
                ],
            );
        }
    }
}
