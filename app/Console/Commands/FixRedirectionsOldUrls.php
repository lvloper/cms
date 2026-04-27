<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Redirection;

class FixRedirectionsOldUrls extends Command
{
    protected $signature = 'redirections:fix-old-urls 
        {--dry-run : Simula sin guardar}
        {--force : No pedir confirmación}';
    
    protected $description = 'Corrige las old_url de redirecciones que fueron generadas sin incluir el path padre (ej: /dengue -> /informacion/dengue)';

    private $wpPageCache = [];

    public function handle()
    {
        $dry = $this->option('dry-run');
        $force = $this->option('force');

        $this->info('Cargando estructura de páginas de WordPress...');
        
        // Cargar todas las páginas WP con sus parents
        $wpPages = DB::connection('wordpress')->select(
            "SELECT ID, post_name, post_title, post_parent 
             FROM wp_posts 
             WHERE post_type = 'page' 
             AND post_status = 'publish' 
             AND (post_password = '' OR post_password IS NULL)"
        );

        // Indexar por ID y por slug
        foreach ($wpPages as $page) {
            $this->wpPageCache[$page->ID] = $page;
        }

        $this->info('Total páginas WP cargadas: ' . count($this->wpPageCache));

        // Obtener todas las redirecciones activas
        $redirections = Redirection::where('is_active', true)->get();

        $this->info('Total redirecciones a revisar: ' . $redirections->count());

        $updated = 0;
        $skipped = 0;
        $errors = 0;
        $updates = [];

        foreach ($redirections as $redirection) {
            // Extraer el slug desde old_url (ej: /dengue => dengue)
            $oldSlug = trim($redirection->old_url, '/');
            
            // Buscar la página WP por slug
            $wpPage = $this->findPageBySlug($oldSlug);
            
            if (!$wpPage) {
                $skipped++;
                continue;
            }

            // Si la página no tiene parent, la old_url ya está correcta
            if (!$wpPage->post_parent || $wpPage->post_parent == 0) {
                $skipped++;
                continue;
            }

            // Construir el path completo incluyendo parents
            $fullPath = $this->buildFullPath($wpPage);
            
            if (!$fullPath) {
                $this->warn("No se pudo construir path completo para: {$oldSlug}");
                $errors++;
                continue;
            }

            // Normalizar el nuevo path
            $correctedOldUrl = '/' . trim($fullPath, '/');
            
            // Si ya tiene el path correcto, saltar
            if ($redirection->old_url === $correctedOldUrl) {
                $skipped++;
                continue;
            }

            // Verificar que no exista otra redirección con el mismo old_url corregido
            $exists = Redirection::where('old_url', $correctedOldUrl)
                ->where('id', '!=', $redirection->id)
                ->exists();
            
            if ($exists) {
                $this->warn("Ya existe una redirección con old_url: {$correctedOldUrl}");
                $skipped++;
                continue;
            }

            // Guardar para actualizar
            $updates[] = [
                'id' => $redirection->id,
                'current_old_url' => $redirection->old_url,
                'corrected_old_url' => $correctedOldUrl,
                'new_url' => $redirection->new_url ?? '[VACÍO]',
                'wp_title' => $wpPage->post_title ?? ''
            ];
        }

        if (empty($updates)) {
            $this->info('No se encontraron redirecciones que necesiten corrección.');
            return 0;
        }

        // Mostrar preview de cambios
        $this->newLine();
        $this->line('═══════════════════════════════════════════════════════════════════');
        $this->info('CORRECCIONES PROPUESTAS PARA OLD_URL:');
        $this->line('═══════════════════════════════════════════════════════════════════');
        
        $headers = ['ID', 'Current Old URL', 'Corrected Old URL', 'New URL', 'WP Title'];
        $rows = array_map(function($update) {
            return [
                $update['id'],
                \Illuminate\Support\Str::limit($update['current_old_url'], 25),
                \Illuminate\Support\Str::limit($update['corrected_old_url'], 25),
                \Illuminate\Support\Str::limit($update['new_url'], 20),
                \Illuminate\Support\Str::limit($update['wp_title'], 25)
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

        if (!$force && !$this->confirm('¿Deseas proceder con la corrección?', true)) {
            $this->warn('Operación cancelada.');
            return 1;
        }

        // Realizar las actualizaciones
        $this->info('Corrigiendo old_url de redirecciones...');
        $progressBar = $this->output->createProgressBar(count($updates));

        foreach ($updates as $update) {
            try {
                $redirection = Redirection::find($update['id']);
                if ($redirection) {
                    $redirection->old_url = $update['corrected_old_url'];
                    $redirection->description = trim(($redirection->description ?? '') . ' [old_url corregida con parent path]');
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

        // Buscar el parent en el cache
        $parent = $this->wpPageCache[$wpPage->post_parent] ?? null;
        
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
     * Busca una página WP por slug en el cache
     */
    private function findPageBySlug(string $slug)
    {
        // Primero intentar coincidencia exacta con el slug completo
        foreach ($this->wpPageCache as $page) {
            if ($page->post_name === $slug) {
                return $page;
            }
        }
        
        // Si el slug contiene barras, intentar con la última parte
        if (str_contains($slug, '/')) {
            $slugParts = explode('/', $slug);
            $lastPart = end($slugParts);
            
            foreach ($this->wpPageCache as $page) {
                if ($page->post_name === $lastPart) {
                    return $page;
                }
            }
        }
        
        return null;
    }
}
