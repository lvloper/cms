<?php

namespace Database\Seeders;

use App\Enums\Status;
use App\Models\Blog;
use App\Models\Menu;
use App\Models\Page;
use App\Models\Route;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedSuperAdmin();
        $home = $this->seedPage('Home', 'home', $this->homeBlocks(), 'home');
        $about = $this->seedPage('Acerca del CMS', 'acerca-del-cms', $this->aboutBlocks(), 'default');
        $components = $this->seedPage('Componentes base', 'componentes-base', $this->componentsBlocks(), 'hasIndex');

        $blogIndex = $this->seedPage('Novedades', 'novedades', $this->newsIndexBlocks(), 'default');
        $posts = $this->seedPosts($blogIndex->route);

        $this->seedMenu([$home, $about, $components, $blogIndex], $posts);
    }

    private function seedSuperAdmin(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'admin@socies.agency'],
            [
                'name' => 'Socies Admin',
                'password' => Hash::make('123456'),
            ]
        );

        $role = Role::firstOrCreate([
            'name' => config('filament-shield.super_admin.name', 'super_admin'),
            'guard_name' => 'web',
        ]);

        $permissionNames = collect([
            'view_any_page', 'view_page', 'create_page', 'update_page', 'delete_page', 'delete_any_page', 'force_delete_page', 'force_delete_any_page', 'restore_page', 'restore_any_page', 'replicate_page', 'reorder_page',
            'view_any_blog', 'view_blog', 'create_blog', 'update_blog', 'delete_blog', 'delete_any_blog', 'force_delete_blog', 'force_delete_any_blog', 'restore_blog', 'restore_any_blog', 'replicate_blog', 'reorder_blog',
            'view_any_menu', 'view_menu', 'create_menu', 'update_menu', 'delete_menu', 'delete_any_menu', 'force_delete_menu', 'force_delete_any_menu', 'restore_menu', 'restore_any_menu', 'replicate_menu', 'reorder_menu',
            'view_any_banner', 'view_banner', 'create_banner', 'update_banner', 'delete_banner', 'delete_any_banner', 'force_delete_banner', 'force_delete_any_banner', 'restore_banner', 'restore_any_banner', 'replicate_banner', 'reorder_banner',
            'view_any_configuration', 'view_configuration', 'create_configuration', 'update_configuration', 'delete_configuration', 'delete_any_configuration', 'force_delete_configuration', 'force_delete_any_configuration', 'restore_configuration', 'restore_any_configuration', 'replicate_configuration', 'reorder_configuration',
            'view_any_redirection', 'view_redirection', 'create_redirection', 'update_redirection', 'delete_redirection', 'delete_any_redirection', 'force_delete_redirection', 'force_delete_any_redirection', 'restore_redirection', 'restore_any_redirection', 'replicate_redirection', 'reorder_redirection',
            'view_any_user', 'view_user', 'create_user', 'update_user', 'delete_user', 'delete_any_user', 'force_delete_user', 'force_delete_any_user', 'restore_user', 'restore_any_user', 'replicate_user', 'reorder_user',
            'view_role', 'view_any_role', 'create_role', 'update_role', 'delete_role',
        ])->unique();

        $permissions = $permissionNames->map(fn (string $name) => Permission::firstOrCreate([
            'name' => $name,
            'guard_name' => 'web',
        ]));

        $role->syncPermissions($permissions);
        $user->assignRole($role);
    }

    private function seedPage(string $title, string $slug, array $blocks, string $layout = 'default'): Page
    {
        $page = Page::updateOrCreate(
            ['name' => $title],
            ['blocks' => $blocks]
        );

        $page->route()->updateOrCreate(
            ['routable_type' => Page::class, 'routable_id' => $page->id],
            [
                'title' => $title,
                'slug' => $slug,
                'layout' => $layout,
                'status' => Status::Published,
                'full_slug' => $slug,
                'parent_id' => null,
                'description' => "Página de ejemplo: {$title}",
            ]
        );

        return $page->fresh('route');
    }

    private function seedPosts(Route $parentRoute): array
    {
        $posts = [
            [
                'title' => 'Cómo empezar con el CMS base',
                'slug' => 'como-empezar-con-el-cms-base',
                'description' => 'Una guía rápida para cargar páginas, bloques y enlaces internos.',
            ],
            [
                'title' => 'Buenas prácticas para armar páginas modulares',
                'slug' => 'buenas-practicas-paginas-modulares',
                'description' => 'Criterios simples para combinar hero, cards, métricas y CTAs.',
            ],
            [
                'title' => 'Checklist antes de publicar contenido',
                'slug' => 'checklist-antes-de-publicar-contenido',
                'description' => 'Qué revisar antes de pasar una página o novedad a publicado.',
            ],
            [
                'title' => 'Nuevos bloques base disponibles',
                'slug' => 'nuevos-bloques-base-disponibles',
                'description' => 'Un repaso de los componentes genéricos incluidos en el builder.',
            ],
        ];

        return collect($posts)->map(function (array $post, int $index) use ($parentRoute): Blog {
            $blog = Blog::updateOrCreate(
                ['published_at' => now()->subDays(4 - $index)->startOfDay()],
                [
                    'description' => '<p>'.$post['description'].'</p>',
                    'content' => '<p>'.$post['description'].' Este contenido fue creado por el seeder para validar el flujo de crear, editar, publicar y borrar novedades desde Filament.</p>',
                    'image' => null,
                ]
            );

            $blog->route()->updateOrCreate(
                ['routable_type' => Blog::class, 'routable_id' => $blog->id],
                [
                    'title' => $post['title'],
                    'slug' => $post['slug'],
                    'layout' => 'default',
                    'status' => Status::Published,
                    'parent_id' => $parentRoute->id,
                    'full_slug' => $parentRoute->full_slug . '/' . $post['slug'],
                    'description' => $post['description'],
                ]
            );

            return $blog->fresh('route');
        })->all();
    }

    private function seedMenu(array $pages, array $posts): void
    {
        $items = collect($pages)->map(fn (Page $page): array => [
            '_token' => 'page-' . $page->id,
            'label' => $page->route->title,
            'order' => $page->id,
            'route' => $this->routeAttrs($page->route),
            'children' => [],
        ])->values()->all();

        if (! empty($posts)) {
            $items[] = [
                '_token' => 'post-featured',
                'label' => 'Última novedad',
                'order' => count($items),
                'route' => $this->routeAttrs($posts[0]->route),
                'children' => [],
            ];
        }

        Menu::updateOrCreate(
            ['slug' => 'header'],
            [
                'name' => 'Header',
                'items' => $items,
            ]
        );
    }

    private function routeAttrs(?Route $route, ?string $label = null): array
    {
        return [
            'btn_label' => $label,
            'route_id' => $route?->id ? (string) $route->id : null,
            'external_url' => null,
            'file' => null,
            'download_name' => null,
            'anchor' => null,
            'new_window' => false,
        ];
    }

    private function homeBlocks(): array
    {
        return [
            $this->block('BaseRichText', [
                'blockTitle' => 'Inicio',
                'eyebrow' => 'CMS Base',
                'title' => 'Una base limpia para construir sitios editables',
                'content' => '<p>Este proyecto incluye rutas dinámicas, page builder, novedades, menús, configuraciones y permisos listos para personalizar.</p>',
                'width' => 'wide',
            ]),
            $this->block('BaseCards', [
                'blockTitle' => 'Funcionalidades',
                'title' => 'Qué trae listo',
                'description' => '<p>Bloques y recursos genéricos para acelerar nuevos proyectos.</p>',
                'items' => [
                    ['title' => 'Page builder', 'description' => 'Bloques reordenables y clonables.', 'image' => null, 'route' => []],
                    ['title' => 'Rutas y SEO', 'description' => 'URLs jerárquicas con estado de publicación.', 'image' => null, 'route' => []],
                    ['title' => 'Admin Filament', 'description' => 'CRUDs para contenido, menú y configuración.', 'image' => null, 'route' => []],
                ],
            ]),
            $this->block('BaseStats', [
                'blockTitle' => 'Métricas',
                'title' => 'Base operativa',
                'items' => [
                    ['value' => '7', 'label' => 'Bloques base', 'description' => 'Componentes genéricos nuevos.'],
                    ['value' => '3', 'label' => 'Páginas demo', 'description' => 'Contenido inicial para explorar.'],
                    ['value' => '4', 'label' => 'Novedades', 'description' => 'Posts de ejemplo publicados.'],
                ],
            ]),
            $this->block('BaseCta', [
                'blockTitle' => 'CTA',
                'eyebrow' => 'Siguiente paso',
                'title' => 'Entrá al panel y empezá a editar',
                'description' => '<p>Usuario inicial: admin@socies.agency</p>',
                'variant' => 'accent',
                'primary_route' => ['btn_label' => 'Ir al admin', 'route_id' => '0', 'external_url' => url('/admin'), 'new_window' => true],
                'secondary_route' => [],
            ]),
        ];
    }

    private function aboutBlocks(): array
    {
        return [
            $this->block('BaseRichText', [
                'blockTitle' => 'Acerca',
                'eyebrow' => 'Arquitectura',
                'title' => 'CMS Laravel + Filament',
                'content' => '<p>Las páginas se componen con bloques, las rutas resuelven el contenido publicado y Filament concentra la administración.</p>',
                'width' => 'container',
            ]),
            $this->block('BaseQuote', [
                'blockTitle' => 'Filosofía',
                'quote' => 'Menos variantes específicas, más componentes base bien compuestos.',
                'author' => 'Socies CMS',
                'source' => 'Seeder inicial',
                'image' => null,
            ]),
            $this->block('BaseLinkList', [
                'blockTitle' => 'Accesos',
                'title' => 'Atajos útiles',
                'items' => [
                    ['route' => ['btn_label' => 'Panel de administración', 'route_id' => '0', 'external_url' => url('/admin'), 'new_window' => true], 'description' => 'Ingresar al panel Filament.'],
                    ['route' => ['btn_label' => 'Sitemap', 'route_id' => '0', 'external_url' => url('/sitemap.xml'), 'new_window' => true], 'description' => 'Ver sitemap generado.'],
                ],
            ]),
        ];
    }

    private function componentsBlocks(): array
    {
        return [
            $this->block('BaseRichText', [
                'blockTitle' => 'Texto',
                'title' => 'Texto enriquecido',
                'content' => '<p>Bloque para contenido editorial simple con ancho configurable.</p>',
                'width' => 'narrow',
            ]),
            $this->block('BaseCards', [
                'blockTitle' => 'Cards',
                'title' => 'Cards genéricas',
                'items' => [
                    ['title' => 'Card uno', 'description' => 'Descripción breve.', 'image' => null, 'route' => []],
                    ['title' => 'Card dos', 'description' => 'Descripción breve.', 'image' => null, 'route' => []],
                    ['title' => 'Card tres', 'description' => 'Descripción breve.', 'image' => null, 'route' => []],
                ],
            ]),
            $this->block('BaseStats', [
                'blockTitle' => 'Stats',
                'title' => 'Métricas',
                'items' => [
                    ['value' => '+120', 'label' => 'Registros', 'description' => 'Dato de ejemplo.'],
                    ['value' => '24/7', 'label' => 'Disponible', 'description' => 'Dato de ejemplo.'],
                    ['value' => '100%', 'label' => 'Editable', 'description' => 'Dato de ejemplo.'],
                ],
            ]),
            $this->block('BaseEmbed', [
                'blockTitle' => 'Embed',
                'title' => 'Embed seguro de ejemplo',
                'embed' => '<iframe src="https://www.openstreetmap.org/export/embed.html?bbox=-58.45%2C-34.62%2C-58.35%2C-34.55&amp;layer=mapnik"></iframe>',
                'caption' => 'Ejemplo de iframe embebido.',
            ]),
        ];
    }

    private function newsIndexBlocks(): array
    {
        return [
            $this->block('BaseRichText', [
                'blockTitle' => 'Novedades',
                'eyebrow' => 'Blog',
                'title' => 'Novedades de ejemplo',
                'content' => '<p>Esta página funciona como índice editorial inicial. Las novedades tienen rutas hijas publicadas.</p>',
                'width' => 'container',
            ]),
        ];
    }

    private function block(string $type, array $data): array
    {
        return [
            'type' => $type,
            'data' => array_merge([
                'blockTitle' => null,
                'blockAnchor' => null,
                'mb' => 'mb-12',
                'mdMb' => 'md:mb-0',
                'clases' => [],
                'styles' => [],
                'stylesMd' => [],
                'hidden' => false,
            ], $data),
        ];
    }

}
