<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Carga trabajos ficticios en Cliente > Trabajos para probar el sitio y el
 * futuro recuperador de evidencia de Paco antes de tener casos reales.
 *
 * Este seeder nunca reemplaza trabajos existentes. Por seguridad solo se puede
 * ejecutar en entornos local o testing.
 */
class ClientWorksDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new RuntimeException(
                'ClientWorksDemoSeeder es solo para entornos local/testing.'
            );
        }

        $seeded = 0;
        $skipped = 0;

        Client::query()
            ->with('route')
            ->orderBy('id')
            ->each(function (Client $client) use (&$seeded, &$skipped): void {
                if ($client->works?->isNotEmpty()) {
                    $skipped++;

                    return;
                }

                $routeSlug = $client->route?->slug ?? Str::slug($client->title);
                $works = $this->worksFor($routeSlug, $client->title);

                $client->forceFill(['works' => $works])->save();
                $seeded++;
            });

        $this->command?->info("Trabajos demo cargados en {$seeded} clientes; {$skipped} clientes con trabajos existentes fueron preservados.");
    }

    /**
     * @return array<int, array{title: string, categories: array<int, string>, external_url: string, image: null, description: string}>
     */
    private function worksFor(string $slug, string $clientTitle): array
    {
        $works = [
            'fundacion-huesped' => [
                [
                    'title' => 'Demo — Plataforma institucional y de campañas',
                    'categories' => ['design', 'programming', 'strategy'],
                    'external_url' => 'https://demo.socies.local/fundacion-huesped/plataforma-institucional',
                    'image' => null,
                    'description' => 'Caso ficticio para probar una plataforma institucional con contenidos, campañas y recursos de salud.',
                ],
                [
                    'title' => 'Demo — Landing de campaña de prevención',
                    'categories' => ['marketing', 'design', 'programming'],
                    'external_url' => 'https://demo.socies.local/fundacion-huesped/campana-prevencion',
                    'image' => null,
                    'description' => 'Landing demo orientada a comunicar una campaña, captar consultas y facilitar la actualización del equipo.',
                ],
            ],
            'amnistia-internacional' => [
                [
                    'title' => 'Demo — Nueva plataforma institucional',
                    'categories' => ['design', 'programming', 'strategy'],
                    'external_url' => 'https://demo.socies.local/amnistia-internacional/plataforma',
                    'image' => null,
                    'description' => 'Caso ficticio de modernización web para una organización con múltiples campañas y públicos.',
                ],
                [
                    'title' => 'Demo — Landing de campaña de derechos humanos',
                    'categories' => ['marketing', 'design'],
                    'external_url' => 'https://demo.socies.local/amnistia-internacional/campana',
                    'image' => null,
                    'description' => 'Landing demo para presentar una campaña, ordenar contenidos y acompañar la conversión.',
                ],
            ],
            'eurobursatil' => [
                [
                    'title' => 'Demo — Sitio institucional para servicios financieros',
                    'categories' => ['branding', 'design', 'programming'],
                    'external_url' => 'https://demo.socies.local/eurobursatil/sitio-institucional',
                    'image' => null,
                    'description' => 'Sitio ficticio para explicar servicios financieros con una identidad clara y formularios de contacto.',
                ],
                [
                    'title' => 'Demo — Herramienta digital para consultas comerciales',
                    'categories' => ['programming', 'strategy'],
                    'external_url' => 'https://demo.socies.local/eurobursatil/herramienta-consultas',
                    'image' => null,
                    'description' => 'Prototipo demo de una herramienta para ordenar consultas y oportunidades comerciales.',
                ],
            ],
            'qyt-servicios' => [
                [
                    'title' => 'Demo — Web comercial de servicios',
                    'categories' => ['branding', 'design', 'programming'],
                    'external_url' => 'https://demo.socies.local/qyt-servicios/web-comercial',
                    'image' => null,
                    'description' => 'Caso ficticio para presentar una oferta de servicios, equipos y formas de trabajo.',
                ],
                [
                    'title' => 'Demo — Landing de captación de consultas',
                    'categories' => ['marketing', 'design'],
                    'external_url' => 'https://demo.socies.local/qyt-servicios/landing-consultas',
                    'image' => null,
                    'description' => 'Landing demo con foco en campañas y generación de oportunidades calificadas.',
                ],
            ],
            'fundacion-leloir' => [
                [
                    'title' => 'Demo — Plataforma de divulgación científica',
                    'categories' => ['design', 'programming', 'strategy'],
                    'external_url' => 'https://demo.socies.local/fundacion-leloir/divulgacion',
                    'image' => null,
                    'description' => 'Plataforma ficticia para acercar investigaciones, noticias y recursos a distintos públicos.',
                ],
                [
                    'title' => 'Demo — Campaña digital de investigación',
                    'categories' => ['marketing', 'design'],
                    'external_url' => 'https://demo.socies.local/fundacion-leloir/campana',
                    'image' => null,
                    'description' => 'Campaña demo para comunicar un proyecto de investigación y facilitar su difusión.',
                ],
            ],
            'cedes' => [
                [
                    'title' => 'Demo — Sitio institucional y biblioteca de investigaciones',
                    'categories' => ['design', 'programming', 'strategy'],
                    'external_url' => 'https://demo.socies.local/cedes/biblioteca',
                    'image' => null,
                    'description' => 'Caso ficticio para ordenar publicaciones, autores y líneas de investigación en una experiencia simple.',
                ],
                [
                    'title' => 'Demo — Landing de publicación de informe',
                    'categories' => ['marketing', 'design'],
                    'external_url' => 'https://demo.socies.local/cedes/informe',
                    'image' => null,
                    'description' => 'Landing demo para presentar un informe, resumir sus hallazgos y facilitar su descarga.',
                ],
            ],
            'iidi' => [
                [
                    'title' => 'Demo — Identidad y sitio institucional',
                    'categories' => ['branding', 'design', 'programming'],
                    'external_url' => 'https://demo.socies.local/iidi/sitio-institucional',
                    'image' => null,
                    'description' => 'Trabajo ficticio de identidad y desarrollo web para comunicar una organización con claridad.',
                ],
                [
                    'title' => 'Demo — Sistema de contenidos para proyectos',
                    'categories' => ['programming', 'strategy'],
                    'external_url' => 'https://demo.socies.local/iidi/contenidos',
                    'image' => null,
                    'description' => 'Sistema demo para que el equipo pueda publicar y mantener proyectos sin depender de desarrollo.',
                ],
            ],
        ];

        return $works[$slug] ?? [
            [
                'title' => "Demo — Sitio institucional de {$clientTitle}",
                'categories' => ['design', 'programming'],
                'external_url' => 'https://demo.socies.local/'.Str::slug($clientTitle).'/sitio-institucional',
                'image' => null,
                'description' => 'Trabajo ficticio para probar la carga de proyectos y la evidencia que Paco podrá recuperar del CMS.',
            ],
            [
                'title' => "Demo — Landing de campaña de {$clientTitle}",
                'categories' => ['marketing', 'design'],
                'external_url' => 'https://demo.socies.local/'.Str::slug($clientTitle).'/landing-campana',
                'image' => null,
                'description' => 'Landing ficticia para probar consultas sobre campañas, contenidos y captación.',
            ],
        ];
    }
}
