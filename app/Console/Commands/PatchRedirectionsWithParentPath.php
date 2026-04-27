<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Redirection;
use App\Models\Route;

class PatchRedirectionsWithParentPath extends Command
{
    protected $signature = 'redirections:patch-parent-paths 
        {--dry-run : Simula sin guardar}
        {--force : No pedir confirmación}
        {--check-routes : También buscar en rutas del sistema nuevo}';
    
    protected $description = 'Actualiza las new_url de redirecciones para incluir el path padre cuando la página WP tiene parent o existe en el sistema nuevo';

    private $wpPageCache = [];
    private $routeCache = [];

    public function handle()
    {
        $dry = $this->option('dry-run');
        $force = $this->option('force');
        $checkRoutes = $this->option('check-routes') ?? true; // Por defecto activado

        $this->info('Cargando estructura de páginas de WordPress (con parent)...');
        
        // Cargar todas las páginas WP con sus parents en cache
        $wpPages = DB::connection('wordpress')->select(
            "SELECT ID, post_name, post_title, post_parent 
             FROM wp_posts 
             WHERE post_type = 'page' 
             AND post_status = 'publish' 
             AND (post_password = '' OR post_password IS NULL)"
        );

        foreach ($wpPages as $page) {
            $this->wpPageCache[$page->post_name] = $page;
        }

        $this->info('Total páginas WP cargadas: ' . count($this->wpPageCache));

        // Cargar rutas del sistema nuevo para buscar coincidencias
        if ($checkRoutes) {
            $this->info('Cargando rutas del sistema nuevo...');
            $routes = Route::all(['id', 'slug', 'parent_id', 'full_slug']);
            foreach ($routes as $route) {
                // Indexar por slug simple y por full_slug
                $this->routeCache[$route->slug] = $route;
                if ($route->full_slug) {
                    $slugParts = explode('/', $route->full_slug);
                    $lastSlug = end($slugParts);
                    if (!isset($this->routeCache[$lastSlug])) {
                        $this->routeCache[$lastSlug] = $route;
                    }
                }
            }
            $this->info('Total rutas del sistema nuevo: ' . count($routes));
        }

        // Obtener todas las redirecciones activas
        $redirections = Redirection::where('is_active', true)->get();

        $this->info('Total redirecciones a revisar: ' . $redirections->count());

        $updated = 0;
        $skipped = 0;
        $errors = 0;
        $updates = [];

        foreach ($redirections as $redirection) {
            // Extraer el slug desde old_url (ej: /zika-dengue => zika-dengue)
            $oldSlug = trim($redirection->old_url, '/');
            $slugParts = explode('/', $oldSlug);
            $lastSlugPart = end($slugParts); // Para páginas como /informacion/dengue, tomamos 'dengue'
            
            $proposedPath = null;
            $source = null;

            // Estrategia 1: Buscar en rutas del sistema nuevo
            if ($checkRoutes) {
                // Primero intentar con el slug completo
                if (isset($this->routeCache[$oldSlug])) {
                    $route = $this->routeCache[$oldSlug];
                    $proposedPath = '/' . trim($route->full_slug, '/');
                    $source = 'route-exact';
                } 
                // Luego intentar con la última parte del slug
                elseif (isset($this->routeCache[$lastSlugPart])) {
                    $route = $this->routeCache[$lastSlugPart];
                    $proposedPath = '/' . trim($route->full_slug, '/');
                    $source = 'route-last-part';
                }
            }

            // Estrategia 2: Buscar en páginas WP y construir path con parents
            if (!$proposedPath && isset($this->wpPageCache[$lastSlugPart])) {
                $wpPage = $this->wpPageCache[$lastSlugPart];

                // Si la página tiene parent, construir el path completo
                if ($wpPage->post_parent && $wpPage->post_parent > 0) {
                    $fullPath = $this->buildFullPath($wpPage);
                    
                    if ($fullPath) {
                        $proposedPath = '/' . trim($fullPath, '/');
                        $source = 'wp-parent';
                    }
                }
            }

            // Si no se encontró un nuevo path, saltar
            if (!$proposedPath) {
                $skipped++;
                continue;
            }

            // Si ya tiene el path correcto, saltar
            if ($redirection->new_url === $proposedPath) {
                $skipped++;
                continue;
            }

            // Guardar para actualizar
            $updates[] = [
                'id' => $redirection->id,
                'old_url' => $redirection->old_url,
                'current_new_url' => $redirection->new_url ?? '[VACÍO]',
                'proposed_new_url' => $proposedPath,
                'source' => $source
            ];
        }

        if (empty($updates)) {
            $this->info('No se encontraron redirecciones que necesiten actualización.');
            return 0;
        }

        // Mostrar preview de cambios
        $this->newLine();
        $this->line('═══════════════════════════════════════════════════════════════════');
        $this->info('CAMBIOS PROPUESTOS:');
        $this->line('═══════════════════════════════════════════════════════════════════');
        
        $headers = ['ID', 'Old URL', 'Current New URL', 'Proposed New URL', 'Source'];
        $rows = array_map(function($update) {
            return [
                $update['id'],
                \Illuminate\Support\Str::limit($update['old_url'], 30),
                \Illuminate\Support\Str::limit($update['current_new_url'], 30),
                \Illuminate\Support\Str::limit($update['proposed_new_url'], 30),
                $update['source']
            ];
        }, array_slice($updates, 0, 25)); // Mostrar solo primeros 25
        
        $this->table($headers, $rows);
        
        if (count($updates) > 25) {
            $this->warn('... y ' . (count($updates) - 25) . ' más.');
        }

        $this->newLine();
        $this->info("Total a actualizar: " . count($updates));

        if ($dry) {
            $this->warn('[DRY RUN] No se realizaron cambios.');
            return 0;
        }

        if (!$force && !$this->confirm('¿Deseas proceder con la actualización?', true)) {
            $this->warn('Operación cancelada.');
            return 1;
        }

        // Realizar las actualizaciones
        $this->info('Actualizando redirecciones...');
        $progressBar = $this->output->createProgressBar(count($updates));

        foreach ($updates as $update) {
            try {
                $redirection = Redirection::find($update['id']);
                if ($redirection) {
                    $oldValue = $redirection->new_url;
                    $redirection->new_url = $update['proposed_new_url'];
                    
                    // Actualizar descripción según la fuente
                    $sourceLabels = [
                        'route-exact' => 'Ruta exacta del sistema nuevo',
                        'route-last-part' => 'Ruta encontrada por slug',
                        'wp-parent' => 'Path construido desde WP parent'
                    ];
                    $sourceLabel = $sourceLabels[$update['source']] ?? 'Actualizado';
                    
                    $redirection->description = trim(($redirection->description ?? '') . " [{$sourceLabel}]");
                    $redirection->save();
                    $updated++;
                }
            } catch (\Throwable $e) {
                $errors++;
                $this->error("\nError actualizando ID {$update['id']}: " . $e->getMessage());
            }
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->info("✅ Proceso completado:");
        $this->line("   - Actualizadas: {$updated}");
        $this->line("   - Omitidas: {$skipped}");
        $this->line("   - Errores: {$errors}");

        return 0;
    }

    /**
     * Construye el path completo de una página WP incluyendo todos sus parents
     */
    private function buildFullPath($wpPage, $depth = 0): ?string
    {
        // Protección contra loops infinitos
        if ($depth > 10) {
            return null;
        }

        $slug = $wpPage->post_name ?? '';
        
        if (!$wpPage->post_parent || $wpPage->post_parent == 0) {
            return $slug;
        }

        // Buscar el parent
        $parent = $this->findPageById($wpPage->post_parent);
        
        if (!$parent) {
            return $slug;
        }

        $parentPath = $this->buildFullPath($parent, $depth + 1);
        
        if (!$parentPath) {
            return $slug;
        }

        return $parentPath . '/' . $slug;
    }

    /**
     * Busca una página WP por ID en el cache
     */
    private function findPageById(int $id)
    {
        foreach ($this->wpPageCache as $page) {
            if ($page->ID == $id) {
                return $page;
            }
        }
        return null;
    }
}
