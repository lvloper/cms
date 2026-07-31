<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Paco\Intent;
use Illuminate\Database\Seeder;

final class PacoIntentSeeder extends Seeder
{
    public function run(): void
    {
        $intents = [
            ['landing_page', 'Landing o campaña', 'commercial'],
            ['web_institucional', 'Sitio institucional', 'commercial'],
            ['plataforma_a_medida', 'Sistema o plataforma a medida', 'commercial'],
            ['automatizacion', 'Automatización de procesos', 'commercial'],
            ['integracion', 'Integración entre sistemas', 'commercial'],
            ['mantenimiento', 'Evolución o mantenimiento', 'commercial'],
            ['servicio_mensual', 'Capacidad técnica recurrente', 'commercial'],
            ['consultoria', 'Consulta técnica o de producto', 'commercial'],
            ['partnership', 'Agencia, consultora o socio comercial', 'commercial'],
            ['pack', 'Pack publicado', 'commercial'],
            ['support_existing_client', 'Soporte para cliente actual', 'routing'],
            ['job', 'Búsqueda laboral', 'non_commercial'],
            ['vendor', 'Oferta de proveedor', 'non_commercial'],
            ['general', 'Consulta todavía no clasificada', 'fallback'],
        ];

        foreach ($intents as [$code, $name, $type]) {
            Intent::query()->firstOrCreate(
                ['code' => $code],
                [
                    'name' => $name,
                    'type' => $type,
                    'description' => "Intención base: {$name}",
                    'status' => 'active',
                ],
            );
        }
    }
}
