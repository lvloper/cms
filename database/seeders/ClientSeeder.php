<?php

namespace Database\Seeders;

use App\Enums\Status;
use App\Models\Client;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        $clients = [
            [
                'title' => 'Fundación Huésped',
                'logo' => 'images/clients/logos/fundacion-huesped.webp',
                'color' => '#00A7A0',
                'popup_text_color' => 'black',
                'is_featured' => true,
                'industry' => 'Organizaciones sociales y salud',
                'testimonials' => [
                    [
                        'person' => 'Florencia Gadea',
                        'position' => 'Directora de División de Comunicación',
                        'testimonial' => '<p>Socies nos acompañó durante todo el proceso de rediseño de nuestra web institucional brindando propuestas y soluciones creativas a nuestras necesidades. Destaco <strong>la flexibilidad y la buena predisposición</strong> para llevar adelante un proyecto tan desafiante.</p>',
                    ],
                    [
                        'person' => 'Florencia Sierra',
                        'position' => 'Líder de comunicación digital',
                        'testimonial' => '<p>Teníamos un desafío muy importante y lograron entender a fondo nuestras necesidades, durante todo el proceso hubo una <strong>comunicación súper fluida, acompañamiento constante</strong> y mucha predisposición para resolver cada detalle. Nos quedamos con una <strong>plataforma fácil de gestionar</strong>, con soporte siempre disponible y un equipo que realmente comprendió los desafíos que les presentamos. Fue un trabajo en conjunto del que estamos muy contentxs con el resultado final.</p>',
                    ],
                ],
            ],
            [
                'title' => 'Amnistía Internacional',
                'logo' => 'images/clients/logos/amnistia-internacional.webp',
                'color' => '#FFF200',
                'popup_text_color' => 'black',
                'is_featured' => true,
                'industry' => 'Organizaciones sociales y derechos humanos',
                'testimonials' => [
                    [
                        'person' => 'Laura Durán',
                        'position' => 'Directora de Comunicación y Prensa',
                        'testimonial' => '<p><strong>El equipo lideró con gran profesionalismo</strong> el proceso de migración y modernización de nuestra web. Cada vez que surgió una necesidad urgente, respondieron con rapidez y priorizando nuestras solicitudes. Cuando enfrentamos dudas o cuestiones técnicas complejas, nos brindaron explicaciones claras y dinámicas sobre el diagnóstico y las soluciones posibles. En conclusión, <strong>el cambio hacia la nueva plataforma fue muy fructífero</strong> y la experiencia de trabajo general con ellos resultó y resulta sumamente positiva.</p>',
                    ],
                    [
                        'person' => 'Ambar Chacin',
                        'position' => 'Deputy Director of Growth and Fundraising',
                        'testimonial' => '<p>Desde Amnistía Internacional Argentina valoramos profundamente el trabajo conjunto que realizamos. Destacamos especialmente la capacidad del equipo para comprender nuestras necesidades, ofrecer soluciones efectivas y acompañarnos de manera cercana durante todo el proceso. Socies es una agencia que nos acompaña en el desarrollo de diversas plataformas y estrategias digitales desde hace más de 5 años.</p><p>Recientemente trabajamos en conjunto para la actualización completa de nuestra web y siendo un gran desafío logramos el resultado esperado. <strong>El desarrollo de esta nueva plataforma fue un paso importante para fortalecer nuestra misión de promover los derechos humanos</strong>, y el soporte recibido en cada etapa marcó una gran diferencia.</p><p>Nos alegra haber contado con un equipo tan comprometido y profesional.</p>',
                    ],
                ],
            ],
            [
                'title' => 'Eurobursatil',
                'logo' => 'images/clients/logos/eurobursatil.webp',
                'color' => '#23408E',
                'popup_text_color' => 'white',
                'is_featured' => true,
                'industry' => 'Servicios financieros',
            ],
            [
                'title' => 'QyT Servicios',
                'logo' => 'images/clients/logos/qyt-servicios.webp',
                'color' => '#2D7D8A',
                'popup_text_color' => 'white',
                'is_featured' => true,
                'industry' => 'Servicios profesionales',
            ],
            [
                'title' => 'Fundación Leloir',
                'logo' => 'images/clients/logos/fundacion-leloir.webp',
                'color' => '#713C8C',
                'popup_text_color' => 'white',
                'is_featured' => true,
                'industry' => 'Ciencia e investigación',
            ],
            [
                'title' => 'CEDES',
                'logo' => 'images/clients/logos/cedes.webp',
                'color' => '#E7772E',
                'popup_text_color' => 'black',
                'is_featured' => true,
                'industry' => 'Organizaciones sociales e investigación',
                'testimonials' => [
                    [
                        'person' => 'Mariana Romero',
                        'position' => 'Directora ejecutiva CEDES – Centro de Estudios de Estado y Sociedad',
                        'testimonial' => '<p>“El trabajo con Socies es dinámico y receptivo. Se adaptan a las necesidades. Han podido adaptarse a los requerimientos a la vez que proponer opciones. <strong>El cambio que nos propusieron fue muy significativo y se nota.</strong>”</p>',
                    ],
                ],
            ],
            [
                'title' => 'IIDI',
                'logo' => 'images/clients/logos/iidi.webp',
                'color' => '#E34D61',
                'popup_text_color' => 'black',
                'is_featured' => true,
                'industry' => 'Innovación y desarrollo institucional',
            ],
        ];

        foreach ($clients as $index => $data) {
            $slug = Str::slug($data['title']);
            $testimonials = collect($data['testimonials'] ?? [])
                ->map(fn (array $testimonial): array => [
                    ...$testimonial,
                    'use_authorized' => true,
                    'chat_enabled' => true,
                ])
                ->all();

            $client = Client::query()
                ->whereHas('route', fn ($query) => $query->where('slug', $slug))
                ->first();

            if (! $client) {
                $client = Client::create([
                    'logo' => $data['logo'],
                    'color' => $data['color'],
                    'sort_order' => $index,
                    'popup_text_color' => $data['popup_text_color'],
                    'is_featured' => $data['is_featured'],
                    'public_name' => $data['title'],
                    'industry' => $data['industry'],
                    'paco_summary' => null,
                    'paco_chat_text' => null,
                    'paco_use_authorized' => true,
                    'paco_chat_enabled' => true,
                    'blocks' => [],
                    'works' => [],
                    'testimonials' => $testimonials,
                    'preview_items' => [],
                ]);
            } else {
                $clientData = [
                    'logo' => $data['logo'],
                    'color' => $data['color'],
                    'popup_text_color' => $data['popup_text_color'],
                    'is_featured' => $data['is_featured'],
                    'public_name' => $data['title'],
                    'industry' => $data['industry'],
                    'paco_use_authorized' => true,
                    'paco_chat_enabled' => true,
                ];

                if (array_key_exists('testimonials', $data)) {
                    $incomingTestimonials = collect($testimonials);
                    $incomingPeople = $incomingTestimonials
                        ->map(fn (array $testimonial): string => Str::lower(Str::squish($testimonial['person'] ?? '')))
                        ->filter()
                        ->all();

                    $clientData['testimonials'] = collect($client->testimonials ?? [])
                        ->reject(fn (array $testimonial): bool => in_array(
                            Str::lower(Str::squish($testimonial['person'] ?? '')),
                            $incomingPeople,
                            true,
                        ))
                        ->concat($incomingTestimonials)
                        ->values()
                        ->all();
                }

                $client->update($clientData);
            }

            $client->route()->updateOrCreate(
                [
                    'routable_type' => Client::class,
                    'routable_id' => $client->id,
                ],
                [
                    'title' => $data['title'],
                    'slug' => $slug,
                    'full_slug' => 'cliente/'.$slug,
                    'status' => Status::Published,
                    'parent_id' => null,
                    'layout' => 'default',
                ],
            );
        }
    }
}
