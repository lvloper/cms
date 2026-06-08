<?php

namespace Database\Seeders;

use App\Enums\Status;
use App\Models\Blog;
use App\Models\Configuration;
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
        $blogIndex = $this->seedPage('Novedades', 'novedades', $this->newsIndexBlocks(), 'default');
        $errorPage = $this->seedPage('Error 404', 'error-404', $this->errorBlocks(), 'default');
        $posts = $this->seedPosts($blogIndex->route);
        $this->seedMenu([$home, $blogIndex], $posts);
        $this->seedConfig();
        $this->seedHomeConfig($home);
        $this->seedErrorConfig($errorPage);
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
                'description' => 'Criterios simples para combinar bloques y crear páginas.',
            ],
            [
                'title' => 'Checklist antes de publicar contenido',
                'slug' => 'checklist-antes-de-publicar-contenido',
                'description' => 'Qué revisar antes de pasar una página o novedad a publicado.',
            ],
            [
                'title' => 'Nuevos bloques disponibles',
                'slug' => 'nuevos-bloques-disponibles',
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
            $this->block('Text', [
                'blockTitle' => 'Inicio',
                'eyebrow' => 'CMS Base',
                'title' => 'Una base limpia para construir sitios editables',
                'content' => '<p>Este proyecto incluye rutas dinámicas, page builder, novedades, menús, configuraciones y permisos listos para personalizar.</p>',
                'width' => 'wide',
            ]),
            $this->block('Cards', [
                'blockTitle' => 'Funcionalidades',
                'title' => 'Qué trae listo',
                'description' => '<p>Bloques y recursos genéricos para acelerar nuevos proyectos.</p>',
                'items' => [
                    ['title' => 'Page builder', 'description' => 'Bloques reordenables y clonables.', 'image' => null, 'route' => []],
                    ['title' => 'Rutas y SEO', 'description' => 'URLs jerárquicas con estado de publicación.', 'image' => null, 'route' => []],
                    ['title' => 'Admin Filament', 'description' => 'CRUDs para contenido, menú y configuración.', 'image' => null, 'route' => []],
                ],
            ]),
        ];
    }

    private function newsIndexBlocks(): array
    {
        return [
            $this->block('Text', [
                'blockTitle' => 'Novedades',
                'eyebrow' => 'Blog',
                'title' => 'Novedades',
                'content' => '<p>Últimas novedades y actualizaciones.</p>',
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

    private function seedConfig(): void
    {
        Configuration::updateOrCreate(
            ['key' => 'site-name'],
            [
                'type' => 'text',
                'value' => ['text' => 'CMS Base'],
            ]
        );
    }

    private function seedHomeConfig(Page $home): void
    {
        Configuration::updateOrCreate(
            ['key' => 'home_route_id'],
            [
                'type' => 'url',
                'value' => [
                    'route' => $this->routeAttrs($home->route),
                ],
            ]
        );
    }

    private function seedErrorConfig(Page $errorPage): void
    {
        Configuration::updateOrCreate(
            ['key' => 'error_404_route_id'],
            [
                'type' => 'url',
                'value' => [
                    'route' => $this->routeAttrs($errorPage->route),
                ],
            ]
        );
    }

    private function errorBlocks(): array
    {
        return [
            $this->block('Text', [
                'blockTitle' => 'Error 404',
                'title' => 'Página no encontrada',
                'content' => '<p>La página que buscás no existe o fue movida.</p>',
                'width' => 'narrow',
            ]),
            $this->block('Search', [
                'blockTitle' => 'Búsqueda',
                'title' => 'Buscá en el sitio',
                'description' => 'Usá el buscador para encontrar lo que necesitás.',
            ]),
        ];
    }
}
