<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Redirection;
use App\Models\Route;
use Illuminate\Support\Str;

class GenerateRedirectionsFromOldWP extends Command
{
    protected $signature = 'redirections:generate-from-wp 
        {--dry-run : Simula sin guardar}
        {--limit=0 : Limitar cantidad}
        {--fallback= : (OPCIONAL) URL destino global (ej. /novedades). Si se omite, se deja sin destino}
        {--fresh : TRUNCATE de TODAS las redirecciones antes de generar (reinicio completo)}';
    protected $description = 'Genera redirecciones (borradores sin destino) desde URLs del WordPress viejo que no existen ni tienen redirección. Opción --fresh reinicia la tabla.';

    private $wpPageCache = [];

    public function handle()
    {
        $dry = $this->option('dry-run');
        $limit = (int) $this->option('limit');
        $fallback = $this->option('fallback');
        $fresh = $this->option('fresh');

        if ($fresh) {
            $this->runFreshReset($dry);
        }

        $this->info('Buscando páginas publicadas en WordPress (old site)...');

        // Traer slugs de páginas/posts publicados y públicos (sin contraseña) en WP
        $query = "SELECT ID, post_name, post_title, guid, post_parent FROM wp_posts 
            WHERE post_type = 'page' 
            AND post_status='publish' 
            AND (post_password = '' OR post_password IS NULL)
            ORDER BY ID ASC";
        $rows = DB::connection('wordpress')->select($query);
        
        // Construir cache de páginas por ID y por slug para facilitar lookup
        foreach ($rows as $row) {
            $this->wpPageCache[$row->ID] = $row;
        }
        
        if ($limit > 0) {
            $rows = array_slice($rows, 0, $limit);
        }

        $created = 0; $skipped = 0; $errors = 0;

        foreach ($rows as $row) {
            $slug = (string) ($row->post_name ?? '');
            if ($slug === '' || $slug === 'home' || $slug === 'inicio') {
                $skipped++; continue;
            }

            // Construir el path completo con jerarquía de parents
            $fullPath = $this->buildFullPath($row);
            
            // Normalizar origen del viejo WP como "/path/completo"
            $oldPath = Redirection::normalizePath('/' . ltrim($fullPath, '/'));

            // Si ya existe una Route con ese full_slug, saltar
            $existsRoute = Route::whereFullSlug(ltrim($oldPath, '/'))->exists();
            if ($existsRoute) { $skipped++; continue; }

            // Si ya existe una Redirection activa para ese old_url, saltar
            $existsRedirect = Redirection::where('old_url', $oldPath)->exists();
            if ($existsRedirect) { $skipped++; continue; }

            // Si se define fallback se usa, si no se deja null (borrador sin destino)
            $target = $fallback ? Redirection::normalizePath($fallback) : null;

            if ($dry) {
                $this->line('[DRY] Crear redirección: ' . $oldPath . ' -> ' . ($target ?? '[SIN DESTINO]'));
            } else {
                try {
                    Redirection::create([
                        'old_url' => $oldPath,
                        'new_url' => $target,
                        'redirect_code' => 301,
                        'is_active' => true,
                        'description' => $target ? 'Auto-generada desde WP viejo (fallback)' : 'Auto-generada desde WP viejo (sin destino)'
                    ]);
                    $created++;
                } catch (\Throwable $e) {
                    $errors++;
                    $this->error("Error creando redirección para {$oldPath}: " . $e->getMessage());
                }
            }
        }

        $this->info("Redirecciones creadas: {$created}, saltadas: {$skipped}, errores: {$errors}");
        return 0;
    }

    private function runFreshReset(bool $dry): void
    {
        $total = Redirection::count();
        if ($total === 0) {
            $this->info('Tabla de redirecciones ya está vacía.');
            return;
        }
        if ($dry) {
            $this->line("[DRY] Se eliminarían {$total} redirecciones (TRUNCATE).");
            return;
        }
        if (! $this->confirm("Esto eliminará TODAS ({$total}) las redirecciones. ¿Continuar?")) {
            $this->warn('Operación cancelada.');
            exit(1);
        }
        // Borrar y reiniciar IDs
        Redirection::truncate();
        // Limpieza de cache general (puede ajustarse a un tag si existiera)
        try { cache()->flush(); } catch (\Throwable $e) { /* ignore */ }
        $this->info("Tabla reiniciada. (Se eliminaron {$total} registros)");
    }

    /**
     * Construye el path completo de una página WP incluyendo todos sus parents
     */
    private function buildFullPath($wpPage, $depth = 0): string
    {
        // Protección contra loops infinitos
        if ($depth > 10) {
            return $wpPage->post_name ?? '';
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
        
        return $parentPath . '/' . $slug;
    }
}
