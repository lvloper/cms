<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BlogOldData;
use App\Models\Blog;
use App\Models\Route;
use Carbon\Carbon;

use SimpleXMLElement;


class ScrapeFeedCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrape:feed 
        {--all : Migrar todas las notas (81 páginas)}
        {--page= : Migrar una página específica del feed}
        {--test : Modo test - migrar solo 5 notas para pruebas}
        {--clean : Limpiar todas las notas existentes antes de migrar}
        {--force : Forzar la ejecución en producción (usar con cuidado)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrapea las notas del feed RSS del antiguo blog wordpress de Huésped

    Opciones de migración:
    --all              Migrar todas las notas (81 páginas del feed)
    --page=N           Migrar solo la página N del feed
    --test             Modo test - migrar solo 5 notas para pruebas
    --clean            Limpiar todas las notas existentes antes de migrar
    --force            Forzar la ejecución en producción

    Ejemplos:
    php artisan scrape:feed --test              # Migrar 5 notas de prueba
    php artisan scrape:feed --all               # Migrar todas las notas
    php artisan scrape:feed --page=1            # Migrar solo la página 1
    php artisan scrape:feed --all --clean       # Limpiar todo y migrar de nuevo
    ';

    protected $idPageNews = 5;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (env('APP_ENV') == 'production' && !$this->option('force')) {
            $this->error('No se puede ejecutar en producción. Use --force para forzar la ejecución.');
            return 1;
        }

        // Limpiar notas existentes si se solicita
        if ($this->option('clean')) {
            if ($this->confirm('¿Está seguro de que desea eliminar todas las notas existentes?')) {
                $this->cleanExistingNotes();
            } else {
                $this->info('Operación cancelada.');
                return 0;
            }
        }

        $this->info('Iniciando scraping del feed...');

        try {
            if ($this->option('test')) {
                $this->info('Modo test: migrando solo 5 notas...');
                $this->scrapeFeedTest();
            } elseif ($this->option('all')) {
                $this->info('Migrando todas las notas...');
                $this->scrapeFeedAll();
            } elseif ($this->option('page')) {
                $page = $this->option('page');
                $this->info("Migrando página {$page}...");
                $this->scrapeFeed($page);
            } else {
                $this->error('Debe especificar una opción: --all, --page=N o --test');
                return 1;
            }
        } catch (\Exception $e) {
            $this->error("Error durante el scraping: " . $e->getMessage());
            return 1;
        }

        return 0;
    }

    protected function cleanExistingNotes()
    {
        $this->info('Limpiando notas existentes...');
        
        // Deshabilitar temporalmente las restricciones de clave foránea
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        try {
            // Obtener todos los blogs antes de eliminar
            $blogCount = Blog::count();
            $oldDataCount = BlogOldData::count();
            
            // Eliminar las rutas asociadas a los blogs
            $routes = Route::whereHas('routable', function($query) {
                $query->where('routable_type', 'App\\Models\\Blog');
            })->delete();
            
            // Truncar las tablas
            Blog::truncate();
            BlogOldData::truncate();
            
            $this->info("Se eliminaron {$blogCount} notas y {$oldDataCount} registros de datos antiguos.");
            
        } finally {
            // Volver a habilitar las restricciones de clave foránea
            \DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }

    protected function scrapeFeedTest()
    {
        // Obtener solo las primeras 5 notas de la primera página
        $feedContent = file_get_contents('https://huesped.org.ar/feed/?paged=1');
        $xml = new SimpleXMLElement($feedContent);
        
        $count = 0;
        $limit = 5;
        
        foreach ($xml->channel->item as $item) {
            if ($count >= $limit) break;
            
            $this->processItem($item);
            $count++;
            sleep(1); // Esperar entre requests para no sobrecargar
        }
        
        $this->info("Modo test completado. Se procesaron {$count} artículos.");
    }

    protected function scrapeFeedAll()
    {
        set_time_limit(0);
        $totalPages = 81;
        $totalProcessed = 0;

        for ($page = 1; $page <= $totalPages; $page++) {
            $this->info("Procesando página {$page} de {$totalPages}...");
            $processed = $this->scrapeFeed($page);
            $totalProcessed += $processed;
            
            if ($page < $totalPages) {
                sleep(3); // Pausa entre páginas
            }
        }
        
        $this->info("Migración completa. Se procesaron {$totalProcessed} artículos en total.");
    }

    protected function scrapeFeed($page)
    {
        $feedContent = file_get_contents('https://huesped.org.ar/feed/?paged=' . $page);
        $xml = new SimpleXMLElement($feedContent);

        $count = 0;

        foreach ($xml->channel->item as $item) {
            $this->processItem($item);
            $count++;
            sleep(1); // Pausa entre items
        }

        $this->line("Página {$page} completada. Se procesaron {$count} artículos.");
        return $count;
    }

    protected function processItem($item)
    {
        // Extraer datos del item
        $title = (string)$item->title;
        $description = (string)$item->description;
        $content = (string)$item->children('content', true);
        $pubDate = Carbon::parse((string)$item->pubDate);
        $oldLink = (string)$item->link;

        $oldId = preg_replace('/[^0-9]/', '', (string)$item->guid);

        // Buscar imagen destacada
        $image = null;
        if (preg_match('/<img.+src=[\'"](?P<src>.+?)[\'"].*>/i', $content, $matches)) {
            $image = $matches['src'];
        }

        $this->createPost(
            oldLink: $oldLink,
            oldId: $oldId,
            title: $title,
            description: $description,
            content: $content,
            image: $image,
            pubDate: $pubDate
        );
    }

    protected function getExtraData($url)
    {
        try {
            $context = stream_context_create([
                'http' => [
                    'timeout' => 10,
                    'user_agent' => 'Mozilla/5.0 (compatible; ScrapeFeedCommand/1.0)'
                ]
            ]);
            
            $html = @file_get_contents($url, false, $context);

            if (!$html) {
                $this->warn("No se pudo obtener contenido de: {$url}");
                return null;
            }

            $dom = new \DOMDocument();
            @$dom->loadHTML($html, LIBXML_NOERROR);
            $xpath = new \DOMXPath($dom);

            // Buscar og:image primero
            $ogImage = null;
            $metaTags = $xpath->query('//meta[@property="og:image"]');
            if ($metaTags->length > 0) {
                $ogImage = $metaTags->item(0)->getAttribute('content');
            }

            // Si no hay og:image, buscar imagen en el contenido
            $contentImage = null;
            if (!$ogImage) {
                $imageNode = $xpath->query('//*[@id="the-post"]//img')->item(0);
                $contentImage = $imageNode ? $imageNode->getAttribute('src') : null;
            }

            // Extraer tags
            $tags = [];
            $tagNodes = $xpath->query('//p[contains(@class, "post-tag")]//a');
            foreach ($tagNodes as $tag) {
                $tags[] = trim($tag->textContent);
            }

            return [
                'og_image' => $ogImage,
                'content_image' => $contentImage,
                'tags' => $tags
            ];
        } catch (\Exception $e) {
            $this->warn("Error al obtener datos extra de {$url}: " . $e->getMessage());
            return null;
        }
    }

    protected function createPost($oldLink, $oldId, $title, $description, $content, $image, $pubDate)
    {
        $slug = str($title)->slug();

        // Verificar si ya existe
        if (BlogOldData::where('old_id', $oldId)->exists()) {
            $this->line("El artículo '{$title}' (ID: {$oldId}) ya existe. Omitiendo...");
            return;
        }

        if (Route::where('slug', $slug)->exists()) {
            // Si el slug existe, agregar un sufijo
            $originalSlug = $slug;
            $counter = 1;
            while (Route::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }
            $this->warn("Slug duplicado detectado. Usando: {$slug}");
        }

        $this->info("Procesando: {$title}");

        // Obtener datos extra incluyendo og:image
        $extraData = $this->getExtraData($oldLink);

        // Determinar la mejor imagen disponible
        $finalImage = null;
        if ($extraData) {
            // Prioridad: og:image > content_image > imagen del feed
            $finalImage = $extraData['og_image'] ?? $extraData['content_image'] ?? $image;
        } else {
            $finalImage = $image;
        }

        // Crear el blog
        $blog = Blog::create([
            'title' => $title,
            'description' => $description,
            'content' => $content,
            'image' => $finalImage,
            'published_at' => $pubDate,
        ]);

        // Crear la ruta
        $blog->route()->create([
            'title' => $title,
            'status' => \App\Enums\Status::Published,
            'slug' => $slug,
            'parent_id' => $this->idPageNews,
            'published_at' => $pubDate,
            'full_slug' => 'novedades/' . $slug,
        ]);

        // Guardar datos antiguos
        BlogOldData::create([
            'old_id' => $oldId,
            'new_id' => $blog->id,
            'title' => $title,
            'old_link' => $oldLink,
            'description' => $description,
            'content' => $content,
            'image' => $finalImage,
            'published_at' => $pubDate,
        ]);

        $this->line("✓ Artículo creado: {$title}");
        if ($finalImage) {
            $this->line("  → Imagen: {$finalImage}");
        }
    }
}
