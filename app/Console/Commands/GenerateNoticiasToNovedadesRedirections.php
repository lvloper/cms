<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Redirection;
use App\Models\Route;

class GenerateNoticiasToNovedadesRedirections extends Command
{
    protected $signature = 'redirections:generate-noticias-to-novedades 
        {--dry-run : Simula sin guardar}
        {--force : No pedir confirmación}';
    
    protected $description = 'Genera redirecciones de /noticias/* a /novedades/* usando similitud de slugs';

    public function handle()
    {
        $dry = $this->option('dry-run');
        $force = $this->option('force');

        $this->info('Buscando posts del WordPress viejo con post_type="post"...');
        
        // Traer posts (noticias) del WP viejo
        $wpPosts = DB::connection('wordpress')->select(
            "SELECT ID, post_name, post_title 
             FROM wp_posts 
             WHERE post_type = 'post' 
             AND post_status = 'publish'
             AND (post_password = '' OR post_password IS NULL)
             ORDER BY ID DESC"
        );

        $this->info('Total posts encontrados: ' . count($wpPosts));

        // Cargar todas las novedades del sistema nuevo
        $novedadesParentId = config('cms-routes.news_parent_id', 5);
        $novedades = Route::where('parent_id', $novedadesParentId)
            ->where('status', \App\Enums\Status::Published)
            ->get();

        $this->info('Total novedades en sistema nuevo: ' . $novedades->count());
        $this->newLine();

        $created = 0;
        $skipped = 0;
        $errors = 0;
        $updates = [];

        foreach ($wpPosts as $post) {
            $slug = (string) ($post->post_name ?? '');
            
            if (empty($slug)) {
                $skipped++;
                continue;
            }

            $oldUrl = '/noticias/' . $slug;

            // Verificar si ya existe una redirección
            $existsRedirect = Redirection::where('old_url', $oldUrl)->exists();
            if ($existsRedirect) {
                $skipped++;
                continue;
            }

            // Buscar novedad similar
            $similarRoute = $this->findSimilarRoute($slug, $novedades);
            
            if (!$similarRoute) {
                $skipped++;
                continue;
            }

            // Calcular similitud para mostrar
            $normalizedSearch = strtolower(preg_replace('/[-_\s]+/', '', $slug));
            $normalizedRouteSlug = strtolower(preg_replace('/[-_\s]+/', '', $similarRoute->slug));
            similar_text($normalizedSearch, $normalizedRouteSlug, $similarity);
            
            $newUrl = '/' . $similarRoute->full_slug;

            $updates[] = [
                'old_url' => $oldUrl,
                'new_url' => $newUrl,
                'similarity' => round($similarity, 1),
                'wp_title' => $post->post_title ?? ''
            ];
        }

        if (empty($updates)) {
            $this->info('No se encontraron redirecciones para crear.');
            return 0;
        }

        // Mostrar preview
        $this->line('═══════════════════════════════════════════════════════════════════');
        $this->info('REDIRECCIONES PROPUESTAS:');
        $this->line('═══════════════════════════════════════════════════════════════════');
        
        $headers = ['Old URL (WP)', 'New URL (Sistema)', 'Similitud', 'Título WP'];
        $rows = array_map(function($update) {
            return [
                \Illuminate\Support\Str::limit($update['old_url'], 35),
                \Illuminate\Support\Str::limit($update['new_url'], 35),
                $update['similarity'] . '%',
                \Illuminate\Support\Str::limit($update['wp_title'], 30)
            ];
        }, array_slice($updates, 0, 25));
        
        $this->table($headers, $rows);
        
        if (count($updates) > 25) {
            $this->warn('... y ' . (count($updates) - 25) . ' más.');
        }

        $this->newLine();
        $this->info("Total a crear: " . count($updates));

        if ($dry) {
            $this->warn('[DRY RUN] No se crearon redirecciones.');
            return 0;
        }

        if (!$force && !$this->confirm('¿Deseas proceder con la creación?', true)) {
            $this->warn('Operación cancelada.');
            return 1;
        }

        // Crear las redirecciones
        $this->info('Creando redirecciones...');
        $progressBar = $this->output->createProgressBar(count($updates));

        foreach ($updates as $update) {
            try {
                Redirection::create([
                    'old_url' => $update['old_url'],
                    'new_url' => $update['new_url'],
                    'redirect_code' => 301,
                    'is_active' => true,
                    'description' => 'Auto-generada: /noticias → /novedades (similitud: ' . $update['similarity'] . '%)'
                ]);
                $created++;
            } catch (\Throwable $e) {
                $errors++;
                $this->error("\nError creando redirección {$update['old_url']}: " . $e->getMessage());
            }
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->info("✅ Proceso completado:");
        $this->line("   - Creadas: {$created}");
        $this->line("   - Omitidas: {$skipped}");
        $this->line("   - Errores: {$errors}");

        return 0;
    }

    private function findSimilarRoute(string $slug, $routes): ?Route
    {
        $normalizedSearch = strtolower(preg_replace('/[-_\s]+/', '', $slug));
        
        $bestMatch = null;
        $bestSimilarity = 0;
        
        foreach ($routes as $route) {
            $normalizedRouteSlug = strtolower(preg_replace('/[-_\s]+/', '', $route->slug));
            
            similar_text($normalizedSearch, $normalizedRouteSlug, $similarity);
            
            $levenshtein = levenshtein($normalizedSearch, $normalizedRouteSlug);
            $maxLength = max(strlen($normalizedSearch), strlen($normalizedRouteSlug));
            $levenshteinSimilarity = $maxLength > 0 ? (1 - ($levenshtein / $maxLength)) * 100 : 0;
            
            $combinedSimilarity = ($similarity + $levenshteinSimilarity) / 2;
            
            if ($combinedSimilarity > 70 && $combinedSimilarity > $bestSimilarity) {
                $bestSimilarity = $combinedSimilarity;
                $bestMatch = $route;
            }
            
            if ($combinedSimilarity > 95) {
                break;
            }
        }
        
        return $bestMatch;
    }
}
