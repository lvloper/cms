<?php

namespace Database\Seeders;

use App\Enums\Status;
use App\Models\Client;
use Illuminate\Database\Seeder;

class AmnistiaClientCaseSeeder extends Seeder
{
    public function run(): void
    {
        $client = Client::query()
            ->whereHas('route', fn ($query) => $query->where('slug', 'amnistia-internacional'))
            ->first();

        if (! $client) {
            $client = Client::create([
                'logo' => 'images/clients/logos/amnistia-internacional.webp',
                'color' => '#FFF200',
                'popup_text_color' => 'black',
                'is_featured' => true,
                'public_name' => 'Amnistía Internacional Argentina',
                'industry' => 'Organizaciones sociales y derechos humanos',
                'blocks' => [],
                'works' => [],
                'testimonials' => [],
                'preview_items' => [],
            ]);
        }

        $client->update([
            'hero_eyebrow' => 'Amnistía Internacional Argentina',
            'hero_title' => 'Tecnología para acompañar la acción',
            'hero_summary' => 'Desde 2018 acompañamos a Amnistía Internacional Argentina como una extensión de su equipo digital, construyendo y sosteniendo las plataformas que utiliza para informar, movilizar y conectar con miles de personas.',
            'relationship_since' => 'Desde 2018',
            'hero_services' => [
                'Estrategia digital',
                'Diseño UX/UI',
                'Desarrollo',
                'CMS',
                'Automatizaciones',
                'Infraestructura',
            ],
            'hero_media_type' => 'video',
            'hero_media_image' => null,
            'hero_media_video' => null,
            'hero_media_alt' => 'Recorrido por el ecosistema digital de Amnistía Internacional Argentina.',
            'hero_media_placeholder' => 'Reemplazar por imagen/video del sitio principal, campañas, formularios y CMS',
            'hero_media_autoplay' => true,
            'blocks' => $this->blocks(),
        ]);

        $client->route()->updateOrCreate(
            [
                'routable_type' => Client::class,
                'routable_id' => $client->id,
            ],
            [
                'title' => 'Amnistía Internacional',
                'slug' => 'amnistia-internacional',
                'full_slug' => 'cliente/amnistia-internacional',
                'status' => Status::Published,
                'parent_id' => null,
                'layout' => 'default',
                'description' => 'Acompañamiento digital continuo para Amnistía Internacional Argentina.',
            ],
        );

        $this->command?->info('Caso visual de Amnistía Internacional actualizado con bloques genéricos de Cliente.');
    }

    private function blocks(): array
    {
        return [
            $this->block('ClientMarquee', [
                'blockTitle' => 'Acciones',
                'items' => [
                    'Convertimos necesidades complejas en sistemas que funcionan',
                    'Diseñamos plataformas que los equipos pueden hacer evolucionar',
                    'Sostenemos la infraestructura que mantiene cada acción disponible',
                    'Integramos diseño, desarrollo y operación en un acompañamiento continuo',
                ],
                'speed' => 'slow',
                'direction' => 'left',
            ]),
            $this->block('ClientFeature', [
                'blockTitle' => 'Plataforma modular',
                'eyebrow' => 'Plataforma modular',
                'title' => 'Un sistema para crear nuevas acciones',
                'body' => '<p>Diseñamos una plataforma modular para que el equipo de Amnistía pueda crear páginas, causas, campañas, acciones y formularios con autonomía.</p>',
                'outcome' => 'Amnistía crea y gestiona sus causas y campañas. Socies construye y mantiene la plataforma que las hace posibles.',
                'layout' => 'text_left',
                'media' => [
                    $this->media('Home y navegación principal', 'landscape', 'Reemplazar por imagen/video de la home del sitio principal'),
                    $this->media('CMS', 'square', 'Reemplazar por imagen/video del editor y selector de bloques del CMS'),
                    $this->media('Experiencia mobile', 'portrait', 'Reemplazar por imagen/video del sitio navegando en mobile'),
                ],
            ]),
            $this->block('ClientTestimonial', [
                'blockTitle' => 'Testimonio de Laura Durán',
                'eyebrow' => 'Trabajar en continuidad',
                'title' => 'La experiencia del equipo',
                'testimonials' => [
                    [
                        'quote' => 'El equipo lideró con gran profesionalismo el proceso de migración y modernización de nuestra web. Cada vez que surgió una necesidad urgente, respondieron con rapidez y priorizando nuestras solicitudes. Cuando enfrentamos dudas o cuestiones técnicas complejas, nos brindaron explicaciones claras y dinámicas sobre el diagnóstico y las soluciones posibles. En conclusión, el cambio hacia la nueva plataforma fue muy fructífero y la experiencia de trabajo general con ellos resultó y resulta sumamente positiva.',
                        'person' => 'Laura Durán',
                        'role' => 'Directora de Comunicación y Prensa',
                    ],
                ],
            ]),
            $this->block('ClientProjects', [
                'blockTitle' => 'Campañas y experiencias',
                'eyebrow' => 'Campañas y experiencias',
                'title' => 'Diferentes causas, una plataforma flexible',
                'intro' => 'Cada campaña puede tener su propia identidad, estructura y recorrido sin perder consistencia dentro del ecosistema digital de Amnistía.',
                'projects' => [
                    [
                        'eyebrow' => 'Participación',
                        'title' => 'Escribí por los Derechos',
                        'summary' => 'Una experiencia de participación que reúne historias, peticiones y distintas formas de acción en un mismo recorrido.',
                        'tags' => ['Historias', 'Peticiones', 'Formularios', 'Mobile'],
                        ...$this->mediaFields('Reemplazar por imagen/video del recorrido de Escribí por los Derechos'),
                    ],
                    [
                        'eyebrow' => 'Información y acceso',
                        'title' => 'Derecho al aborto',
                        'summary' => 'Un espacio digital capaz de organizar información compleja y ofrecer recorridos claros para distintos públicos.',
                        'tags' => ['Guías', 'Recursos', 'Multimedia', 'Acciones'],
                        ...$this->mediaFields('Reemplazar por imagen/video del sitio Derecho al aborto'),
                    ],
                ],
            ]),
            $this->block('ClientTestimonial', [
                'blockTitle' => 'Testimonio de Ambar Chacin',
                'eyebrow' => null,
                'title' => null,
                'testimonials' => [
                    [
                        'quote' => "Desde Amnistía Internacional Argentina valoramos profundamente el trabajo conjunto que realizamos. Destacamos especialmente la capacidad del equipo para comprender nuestras necesidades, ofrecer soluciones efectivas y acompañarnos de manera cercana durante todo el proceso. Socies es una agencia que nos acompaña en el desarrollo de diversas plataformas y estrategias digitales desde hace más de 5 años.\n\nRecientemente trabajamos en conjunto para la actualización completa de nuestra web y siendo un gran desafío logramos el resultado esperado. El desarrollo de esta nueva plataforma fue un paso importante para fortalecer nuestra misión de promover los derechos humanos, y el soporte recibido en cada etapa marcó una gran diferencia.\n\nNos alegra haber contado con un equipo tan comprometido y profesional.",
                        'person' => 'Ambar Chacin',
                        'role' => 'Deputy Director of Growth and Fundraising',
                    ],
                ],
            ]),
            $this->block('ClientFeature', [
                'blockTitle' => 'Diario de Juicio',
                'eyebrow' => 'Una base reutilizable',
                'title' => 'Diario de Juicio',
                'body' => '<p>Un micrositio administrable y personalizable desde un CMS propio, preparado para adaptarse a diferentes campañas, casos y estructuras de contenido.</p>',
                'outcome' => 'No es solo una página puntual: es una nueva base para futuros micrositios.',
                'layout' => 'text_right',
                'media' => [
                    $this->media('Portada', 'landscape', 'Reemplazar por imagen/video de la portada y cronología de Diario de Juicio'),
                    $this->media('Editor', 'square', 'Reemplazar por imagen/video del CMS creando una sección'),
                    $this->media('Mobile', 'portrait', 'Reemplazar por imagen/video de Diario de Juicio en mobile'),
                ],
            ]),
            $this->block('ClientStatement', [
                'blockTitle' => 'Acompañamiento continuo',
                'eyebrow' => 'Acompañamiento continuo',
                'title' => 'Somos parte de su equipo',
                'body' => '<p>No funcionamos como un proveedor que aparece ante un pedido aislado. Conocemos el ecosistema, entendemos las prioridades y trabajamos junto al equipo para resolver necesidades de diseño, desarrollo, infraestructura y operación digital.</p>',
                'layout' => 'text_right',
                ...$this->mediaFields('Reemplazar por imagen/video de reuniones, talleres o trabajo conjunto con el equipo'),
            ]),
            $this->block('ClientProcess', [
                'blockTitle' => 'Trabajo invisible',
                'eyebrow' => 'Trabajo invisible',
                'title' => 'Lo que ocurre detrás de cada campaña',
                'body' => 'Formularios, integraciones, automatizaciones, envíos, certificados, servidores y monitoreo deben funcionar como un único sistema.',
                'nodes' => [
                    ['label' => 'Formularios', 'detail' => 'Captura y validación de datos.'],
                    ['label' => 'Salesforce', 'detail' => 'Integración con los procesos de la organización.'],
                    ['label' => 'Automatizaciones', 'detail' => 'Tareas coordinadas sin trabajo manual repetitivo.'],
                    ['label' => 'Email', 'detail' => 'Confirmaciones y comunicaciones masivas.'],
                    ['label' => 'Certificados', 'detail' => 'Generación de piezas digitales personalizadas.'],
                    ['label' => 'Infraestructura', 'detail' => 'Servicios preparados para sostener el ecosistema.'],
                    ['label' => 'Backups', 'detail' => 'Copias y recuperación ante incidentes.'],
                    ['label' => 'Monitoreo', 'detail' => 'Alertas y seguimiento continuo.'],
                ],
            ]),
            $this->block('ClientMetrics', [
                'blockTitle' => 'Escala y disponibilidad',
                'eyebrow' => 'Escala y disponibilidad',
                'title' => 'Preparados para responder',
                'body' => 'La infraestructura fue creciendo junto con la organización. La adaptamos, monitoreamos y escalamos para sostener el tráfico cotidiano y responder ante campañas de alta demanda.',
                'layout' => 'text_left',
                'metrics' => [
                    ['value' => '2018', 'label' => 'Inicio de la relación', 'note' => null],
                    ['value' => '30+', 'label' => 'Propiedades digitales', 'note' => 'Pendiente de validación antes de publicar.'],
                    ['value' => '~10.000', 'label' => 'Visitas únicas diarias', 'note' => 'Pendiente de validación antes de publicar.'],
                    ['value' => '24/7', 'label' => 'Monitoreo y operación', 'note' => null],
                ],
                ...$this->mediaFields('Reemplazar por imagen/video de métricas agregadas, monitoreo e infraestructura'),
            ]),
            $this->block('ClientClosing', [
                'blockTitle' => 'Cierre',
                'eyebrow' => 'Una relación en movimiento',
                'title' => 'Una relación que continúa creciendo',
                'body' => 'Cada nueva campaña, necesidad o desafío se integra a un ecosistema que conocemos, mantenemos y hacemos evolucionar desde 2018.',
                'media' => [
                    $this->media('Kit de bienvenida', 'landscape', 'Reemplazar por imagen/video del kit de bienvenida'),
                    $this->media('Certificados', 'square', 'Reemplazar por imagen/video de certificados digitales'),
                    $this->media('Formularios', 'square', 'Reemplazar por imagen/video de formularios de donación o baja'),
                    $this->media('Sitios históricos', 'landscape', 'Reemplazar por imagen/video de sitios y campañas históricas'),
                    $this->media('CMS', 'square', 'Reemplazar por imagen/video de detalles del CMS'),
                    $this->media('Infraestructura', 'square', 'Reemplazar por imagen/video de monitoreo e infraestructura'),
                ],
                'cta' => [
                    'btn_label' => 'Conocé cómo podemos integrarnos a tu equipo',
                    'route_id' => '0',
                    'external_url' => '/hablemos',
                    'file' => null,
                    'download_name' => null,
                    'anchor' => null,
                    'new_window' => false,
                ],
            ]),
        ];
    }

    private function media(string $label, string $format, string $placeholder): array
    {
        return [
            'label' => $label,
            'format' => $format,
            'caption' => null,
            ...$this->mediaFields($placeholder),
        ];
    }

    private function mediaFields(string $placeholder): array
    {
        return [
            'media_type' => 'image',
            'media_image' => null,
            'media_video' => null,
            'media_alt' => null,
            'media_placeholder' => $placeholder,
            'media_autoplay' => false,
        ];
    }

    private function block(string $type, array $data): array
    {
        return [
            'type' => $type,
            'data' => array_merge([
                'blockTitle' => null,
                'blockAnchor' => null,
                'mb' => 'mb-0',
                'mdMb' => 'md:mb-0',
                'clases' => [],
                'styles' => [],
                'stylesMd' => [],
                'hidden' => false,
            ], $data),
        ];
    }
}
