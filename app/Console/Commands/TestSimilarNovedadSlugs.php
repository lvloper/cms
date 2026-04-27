<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Route;
use Illuminate\Support\Facades\DB;

class TestSimilarNovedadSlugs extends Command
{
    protected $signature = 'redirections:test-similar-slugs 
        {--old-slug= : Slug viejo para probar (ej: miradispuestoatodo)}
        {--limit=10 : Cantidad de resultados a mostrar}';
    
    protected $description = 'Prueba el algoritmo de similitud de slugs para novedades';

    public function handle()
    {
        $oldSlug = $this->option('old-slug');
        $limit = (int) $this->option('limit');

        if (!$oldSlug) {
            // Obtener algunos ejemplos del WP viejo
            $this->info('Obteniendo ejemplos de slugs del WordPress viejo...');
            
            $wpPosts = DB::connection('wordpress')->select(
                "SELECT post_name, post_title 
                 FROM wp_posts 
                 WHERE post_type = 'post' 
                 AND post_status = 'publish'
                 AND post_name NOT LIKE '%-%-%-%'
                 ORDER BY RAND()
                 LIMIT 10"
            );

            $this->info('Ejemplos de slugs viejos de WordPress:');
            $this->newLine();

            foreach ($wpPosts as $post) {
                $this->testSlug($post->post_name, $post->post_title);
                $this->newLine();
            }
        } else {
            $this->testSlug($oldSlug);
        }

        return 0;
    }

    private function testSlug(string $slug, ?string $title = null)
    {
        $this->line("═══════════════════════════════════════════");
        $this->info("🔍 Buscando coincidencia para: {$slug}");
        if ($title) {
            $this->line("   Título WP: {$title}");
        }
        $this->line("═══════════════════════════════════════════");

        // Normalizar el slug de búsqueda
        $normalizedSearch = strtolower(preg_replace('/[-_\s]+/', '', $slug));
        $this->line("   Slug normalizado: {$normalizedSearch}");

        // Buscar rutas bajo el parent "novedades"
        $novedadesParentId = config('cms-routes.news_parent_id', 5);
        
        $routes = Route::where('parent_id', $novedadesParentId)
            ->where('status', \App\Enums\Status::Published)
            ->get(['id', 'slug', 'full_slug', 'title', 'parent_id']);

        if ($routes->isEmpty()) {
            $this->warn('   ❌ No se encontraron rutas de novedades publicadas');
            return;
        }

        $results = [];
        
        foreach ($routes as $route) {
            // Normalizar el slug de la ruta
            $normalizedRouteSlug = strtolower(preg_replace('/[-_\s]+/', '', $route->slug));
            
            // Calcular similitud usando similar_text
            similar_text($normalizedSearch, $normalizedRouteSlug, $similarity);
            
            // También calcular Levenshtein distance
            $levenshtein = levenshtein($normalizedSearch, $normalizedRouteSlug);
            $maxLength = max(strlen($normalizedSearch), strlen($normalizedRouteSlug));
            $levenshteinSimilarity = $maxLength > 0 ? (1 - ($levenshtein / $maxLength)) * 100 : 0;
            
            // Combinar ambas métricas
            $combinedSimilarity = ($similarity + $levenshteinSimilarity) / 2;
            
            if ($combinedSimilarity > 50) { // Mostrar solo resultados con >50% similitud
                $results[] = [
                    'route' => $route,
                    'similarity' => $combinedSimilarity,
                    'normalized_slug' => $normalizedRouteSlug
                ];
            }
        }

        // Ordenar por similitud descendente
        usort($results, fn($a, $b) => $b['similarity'] <=> $a['similarity']);

        if (empty($results)) {
            $this->warn('   ❌ No se encontraron coincidencias (similitud < 50%)');
            return;
        }

        // Mostrar los mejores resultados
        $this->newLine();
        $this->info('   📊 Mejores coincidencias:');
        $this->newLine();

        $headers = ['%', 'Slug Actual', 'Slug Normalizado', 'URL Completa', 'Título'];
        $rows = [];

        foreach (array_slice($results, 0, 5) as $result) {
            $route = $result['route'];
            $similarity = round($result['similarity'], 1);
            
            $icon = $similarity > 95 ? '🎯' : ($similarity > 80 ? '✅' : ($similarity > 70 ? '⚠️' : '📍'));
            
            $rows[] = [
                $icon . ' ' . $similarity . '%',
                \Illuminate\Support\Str::limit($route->slug, 25),
                \Illuminate\Support\Str::limit($result['normalized_slug'], 25),
                \Illuminate\Support\Str::limit($route->full_slug, 30),
                \Illuminate\Support\Str::limit($route->title, 30)
            ];
        }

        $this->table($headers, $rows);

        if ($results[0]['similarity'] > 70) {
            $bestMatch = $results[0]['route'];
            $this->info("   ✨ REDIRECCIÓN SUGERIDA:");
            $this->line("      /novedades/{$slug} → /{$bestMatch->full_slug}");
            $this->line("      URL completa: " . url($bestMatch->full_slug));
            
            // Verificar si realmente tiene novedades en el full_slug
            if (!str_starts_with($bestMatch->full_slug, 'novedades/')) {
                $this->warn("      ⚠️  ADVERTENCIA: Esta ruta NO está bajo /novedades/");
                $this->line("      Parent ID: " . ($bestMatch->parent_id ?? 'null'));
            }
        }
    }
}
