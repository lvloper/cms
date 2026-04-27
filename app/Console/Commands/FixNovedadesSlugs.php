<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Blog;
use App\Models\Route;
use Illuminate\Support\Str;

class FixNovedadesSlugs extends Command
{
    protected $signature = 'novedades:fix-slugs 
        {--dry-run : Simula sin guardar}
        {--force : No pedir confirmación}
        {--similarity=80 : Porcentaje mínimo de similitud en título (default: 80)}';
    
    protected $description = 'Compara y actualiza los slugs de novedades para que coincidan con el WordPress antiguo';

    private $wpPostsCache = [];

    public function handle()
    {
        $dry = $this->option('dry-run');
        $force = $this->option('force');
        $minSimilarity = (int) $this->option('similarity');

        $this->info('Cargando posts del WordPress antiguo...');
        
        // Cargar todos los posts publicados de WP
        $wpPosts = DB::connection('wordpress')->select(
            "SELECT ID, post_name, post_title, post_date 
             FROM wp_posts 
             WHERE post_type = 'post' 
             AND post_status = 'publish'
             ORDER BY post_date DESC"
        );

        // Indexar por título normalizado para búsqueda rápida
        foreach ($wpPosts as $post) {
            $normalizedTitle = $this->normalizeTitle($post->post_title);
            $this->wpPostsCache[$normalizedTitle] = $post;
        }

        $this->info('Total posts WP: ' . count($wpPosts));

        // Obtener todos los blogs con sus rutas
        $blogs = Blog::with('route')->get();
        
        $this->info('Total blogs en sistema nuevo: ' . $blogs->count());
        $this->newLine();

        $updates = [];
        $notFound = 0;
        $alreadyCorrect = 0;

        foreach ($blogs as $blog) {
            if (!$blog->route) {
                $this->warn("Blog ID {$blog->id} no tiene ruta asociada");
                continue;
            }

            $currentSlug = $blog->route->slug;
            $currentTitle = $blog->route->title;

            // Buscar el post de WP que mejor coincida con el título
            $wpPost = $this->findBestMatch($currentTitle, $minSimilarity);

            if (!$wpPost) {
                $notFound++;
                continue;
            }

            $wpSlug = $wpPost->post_name;

            // Si el slug ya es correcto, continuar
            if ($currentSlug === $wpSlug) {
                $alreadyCorrect++;
                continue;
            }

            // Verificar que el nuevo slug no esté en uso por otra ruta
            $slugExists = Route::where('slug', $wpSlug)
                ->where('id', '!=', $blog->route->id)
                ->exists();

            if ($slugExists) {
                $this->warn("Slug '{$wpSlug}' ya está en uso. Saltando blog ID {$blog->id}");
                continue;
            }

            $updates[] = [
                'blog_id' => $blog->id,
                'route_id' => $blog->route->id,
                'current_slug' => $currentSlug,
                'new_slug' => $wpSlug,
                'title' => Str::limit($currentTitle, 50),
                'wp_title' => Str::limit($wpPost->post_title, 50)
            ];
        }

        if (empty($updates)) {
            $this->info('No se encontraron slugs que necesiten actualización.');
            $this->line("  - Ya correctos: {$alreadyCorrect}");
            $this->line("  - No encontrados en WP: {$notFound}");
            return 0;
        }

        // Mostrar preview
        $this->newLine();
        $this->line('═══════════════════════════════════════════════════════════════════');
        $this->info('CAMBIOS PROPUESTOS:');
        $this->line('═══════════════════════════════════════════════════════════════════');
        
        $headers = ['Blog ID', 'Slug Actual', 'Slug WP', 'Título'];
        $rows = array_map(function($update) {
            return [
                $update['blog_id'],
                Str::limit($update['current_slug'], 40),
                Str::limit($update['new_slug'], 40),
                $update['title']
            ];
        }, array_slice($updates, 0, 25));
        
        $this->table($headers, $rows);
        
        if (count($updates) > 25) {
            $this->warn('... y ' . (count($updates) - 25) . ' más.');
        }

        $this->newLine();
        $this->info("Resumen:");
        $this->line("  - A actualizar: " . count($updates));
        $this->line("  - Ya correctos: {$alreadyCorrect}");
        $this->line("  - No encontrados: {$notFound}");

        if ($dry) {
            $this->warn('[DRY RUN] No se realizaron cambios.');
            return 0;
        }

        if (!$force && !$this->confirm('¿Deseas proceder con la actualización?', true)) {
            $this->warn('Operación cancelada.');
            return 1;
        }

        // Realizar actualizaciones
        $this->info('Actualizando slugs...');
        $progressBar = $this->output->createProgressBar(count($updates));

        $updated = 0;
        $errors = 0;

        foreach ($updates as $update) {
            try {
                $route = Route::find($update['route_id']);
                if ($route) {
                    $route->slug = $update['new_slug'];
                    
                    // Reconstruir full_slug
                    if ($route->parent_id) {
                        $parent = Route::find($route->parent_id);
                        $route->full_slug = $parent->full_slug . '/' . $update['new_slug'];
                    } else {
                        $route->full_slug = $update['new_slug'];
                    }
                    
                    $route->save();
                    $updated++;
                }
            } catch (\Throwable $e) {
                $errors++;
                $this->error("\nError actualizando Blog ID {$update['blog_id']}: " . $e->getMessage());
            }
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        $this->info("✅ Proceso completado:");
        $this->line("   - Actualizados: {$updated}");
        $this->line("   - Errores: {$errors}");
        $this->line("   - Ya correctos: {$alreadyCorrect}");
        $this->line("   - No encontrados: {$notFound}");

        // Limpiar cache
        $this->info('Limpiando cache...');
        \Artisan::call('cache:clear');
        \Artisan::call('route:clear');

        return 0;
    }

    /**
     * Normaliza un título para comparación
     */
    private function normalizeTitle(string $title): string
    {
        // Convertir a minúsculas, quitar acentos, quitar caracteres especiales
        $title = mb_strtolower($title);
        $title = str_replace(
            ['á', 'é', 'í', 'ó', 'ú', 'ñ', 'ü'],
            ['a', 'e', 'i', 'o', 'u', 'n', 'u'],
            $title
        );
        $title = preg_replace('/[^a-z0-9\s]/', '', $title);
        $title = preg_replace('/\s+/', ' ', $title);
        return trim($title);
    }

    /**
     * Encuentra el post de WP que mejor coincida con el título
     */
    private function findBestMatch(string $title, int $minSimilarity)
    {
        $normalizedTitle = $this->normalizeTitle($title);
        
        // Búsqueda exacta primero
        if (isset($this->wpPostsCache[$normalizedTitle])) {
            return $this->wpPostsCache[$normalizedTitle];
        }

        // Búsqueda por similitud
        $bestMatch = null;
        $bestSimilarity = 0;

        foreach ($this->wpPostsCache as $normalizedWpTitle => $wpPost) {
            similar_text($normalizedTitle, $normalizedWpTitle, $similarity);
            
            if ($similarity > $bestSimilarity && $similarity >= $minSimilarity) {
                $bestSimilarity = $similarity;
                $bestMatch = $wpPost;
            }
        }

        return $bestMatch;
    }
}
