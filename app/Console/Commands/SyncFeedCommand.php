<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BlogOldData;
use App\Models\Blog;
use App\Models\Route;
use Carbon\Carbon;
use SimpleXMLElement;

class SyncFeedCommand extends Command
{
    protected $signature = 'scrape:feed:sync 
        {--import : Importa las notas nuevas encontradas}
        {--pages=81 : Cantidad máxima de páginas a escanear}
        {--fix-images : Reintenta obtener imágenes faltantes de notas ya migradas}
        {--dry-run : Muestra acciones sin escribir cambios}
        {--force : Permite ejecutar en producción}';

    protected $description = 'Escanea el feed RSS buscando nuevas notas y/o corrige imágenes faltantes sin reprocesar todo.';

    protected int $newsParentId = 5; // fallback; se puede mover a config

    public function handle(): int
    {
        if (env('APP_ENV') === 'production' && ! $this->option('force')) {
            $this->error('Ejecutar en prod requiere --force');
            return 1;
        }

        $pages = (int)$this->option('pages');
        $doImport = $this->option('import');
        $fixImages = $this->option('fix-images');
        $dry = $this->option('dry-run');

        $this->newsParentId = (int) (config('cms-routes.news_parent_id') ?? $this->newsParentId);

        $this->info('Escaneando feed (máx '.$pages.' páginas)...');

        $existingOldIds = BlogOldData::pluck('old_id')->all();
        $existingMap = array_fill_keys($existingOldIds, true);

        $newItems = [];
        $scanned = 0; $stop = false; $totalItems = 0;

        for ($page=1; $page <= $pages; $page++) {
            $items = $this->fetchPage($page);
            if (empty($items)) {
                $this->line('Página '.$page.' vacía, deteniendo.');
                break;
            }
            $scanned++;
            foreach ($items as $item) {
                $totalItems++;
                $oldId = $item['old_id'];
                if (!isset($existingMap[$oldId])) {
                    $newItems[$oldId] = $item; // evitar duplicados
                }
            }
            // heurística: si en la página actual todos ya existían y ya encontramos al menos 10 páginas consecutivas sin nuevos, se podría cortar.
            // (simplificada: si no hubo nuevos en esta página y ya hay al menos 5 páginas escaneadas y ya encontramos >0 nuevos antes, continuar; si nunca hubo nuevos y van 5 páginas vacías, salimos)
            // Omitimos heurística compleja por ahora para claridad.
        }

        $this->line('Páginas escaneadas: '.$scanned.' | Items totales vistos: '.$totalItems.' | Nuevos: '.count($newItems));

        if ($newItems) {
            $this->info('Nuevos pendientes: '.count($newItems));
            foreach ($newItems as $i) {
                $this->line(' - ['.$i['old_id'].'] '.$i['title']);
            }
            if ($doImport) {
                $this->newLine();
                $this->info('Importando nuevos...');
                foreach ($newItems as $data) {
                    if ($dry) {
                        $this->line('[DRY] Crearía: '.$data['title']);
                        continue;
                    }
                    $this->importItem($data);
                }
            } else {
                $this->comment('Usa --import para crear las nuevas notas.');
            }
        } else {
            $this->info('No se detectaron notas nuevas.');
        }

        if ($fixImages) {
            $this->newLine();
            $this->info('Buscando notas con imagen faltante...');
            $missing = Blog::where(function($q){
                $q->whereNull('image')->orWhere('image','');
            })->get();

            $count = 0; $fixed = 0;
            foreach ($missing as $blog) {
                $old = $blog->getOldBlog();
                if (! $old) continue; // sólo notas migradas
                $count++;
                $extra = $this->getExtraData($old->old_link);
                $image = $extra['og_image'] ?? $extra['content_image'] ?? $this->firstImgFromContent($old->content);
                if ($image) {
                    if ($dry) {
                        $this->line('[DRY] Asignaría imagen a blog #'.$blog->id.' => '.$image);
                        continue;
                    }
                    $blog->update(['image' => $image]);
                    if (!$old->image) { $old->update(['image' => $image]); }
                    $this->line('Imagen fijada para #'.$blog->id.' => '.$image);
                    $fixed++;
                } else {
                    $this->warn('Sin imagen para #'.$blog->id.' (old '.$old->old_id.')');
                }
            }
            $this->line('Revisión imágenes: '.$count.' candidatos | '.$fixed.' actualizados');
        } else {
            $this->comment('Usa --fix-images para reparar imágenes.');
        }

        $this->info('Sync feed finalizado.');
        return 0;
    }

    private function fetchPage(int $page): array
    {
        try {
            $url = 'https://huesped.org.ar/feed/?paged='.$page;
            $content = @file_get_contents($url);
            if (!$content) return [];
            $xml = new SimpleXMLElement($content);
            $out = [];
            foreach ($xml->channel->item as $item) {
                $title = (string)$item->title;
                $description = (string)$item->description;
                $contentNode = (string)$item->children('content', true);
                $pubDate = Carbon::parse((string)$item->pubDate);
                $oldLink = (string)$item->link;
                $oldId = preg_replace('/[^0-9]/', '', (string)$item->guid);
                $out[] = [
                    'old_id' => $oldId,
                    'title' => $title,
                    'description' => $description,
                    'content' => $contentNode,
                    'pub_date' => $pubDate,
                    'old_link' => $oldLink,
                ];
            }
            return $out;
        } catch (\Exception $e) {
            $this->warn('Error leyendo página '.$page.': '.$e->getMessage());
            return [];
        }
    }

    private function importItem(array $data): void
    {
        // Evitar duplicados por si corren rápido consecutivo
        if (BlogOldData::where('old_id', $data['old_id'])->exists()) {
            $this->line('Ya existe (saltando): '.$data['title']);
            return;
        }

        // Construir slug único
        $slugBase = str($data['title'])->slug();
        $slug = $slugBase; $c=1;
        while (Route::where('slug',$slug)->exists()) { $slug = $slugBase.'-'.$c; $c++; }

        // Obtener imagen (similar a createPost original)
        $extra = $this->getExtraData($data['old_link']);
        $imageCandidate = $extra['og_image'] ?? $extra['content_image'] ?? $this->firstImgFromContent($data['content']);

        $blog = Blog::create([
            'title' => $data['title'],
            'description' => $data['description'],
            'content' => $data['content'],
            'image' => $imageCandidate,
            'published_at' => $data['pub_date'],
        ]);

        $blog->route()->create([
            'title' => $data['title'],
            'status' => \App\Enums\Status::Published,
            'slug' => $slug,
            'parent_id' => $this->newsParentId,
            'published_at' => $data['pub_date'],
            'full_slug' => 'novedades/'.$slug,
        ]);

        BlogOldData::create([
            'old_id' => $data['old_id'],
            'new_id' => $blog->id,
            'title' => $data['title'],
            'old_link' => $data['old_link'],
            'description' => $data['description'],
            'content' => $data['content'],
            'image' => $imageCandidate,
            'published_at' => $data['pub_date'],
        ]);

        $this->line('Creado: '.$data['title']);
        if ($imageCandidate) $this->line('  → Imagen: '.$imageCandidate);
    }

    private function getExtraData(string $url): ?array
    {
        try {
            $context = stream_context_create([
                'http' => [
                    'timeout' => 10,
                    'user_agent' => 'Mozilla/5.0 (compatible; SyncFeedCommand/1.0)'
                ]
            ]);
            $html = @file_get_contents($url, false, $context);
            if (!$html) return null;
            $dom = new \DOMDocument();
            @$dom->loadHTML($html, LIBXML_NOERROR);
            $xpath = new \DOMXPath($dom);
            $og = $xpath->query('//meta[@property="og:image"]');
            $ogImage = null;
            if ($og->length) {
                $ogNode = $og->item(0);
                if ($ogNode instanceof \DOMElement) {
                    $ogImage = $ogNode->getAttribute('content');
                }
            }
            $contentImg = null;
            if (! $ogImage) {
                $node = $xpath->query('//*[@id="the-post"]//img')->item(0);
                if ($node instanceof \DOMElement) {
                    $contentImg = $node->getAttribute('src');
                }
            }
            return [
                'og_image' => $ogImage,
                'content_image' => $contentImg,
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    private function firstImgFromContent(?string $html): ?string
    {
        if (!$html) return null;
        if (preg_match('/<img[^>]+src=["\'](?P<src>[^"\']+)["\']/i', $html, $m)) {
            return $m['src'];
        }
        return null;
    }
}
