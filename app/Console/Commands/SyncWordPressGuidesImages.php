<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Material;
use Carbon\Carbon;

class SyncWordPressGuidesImages extends Command
{
    protected $signature = 'import:wordpress-guides:sync {--fix-images : Intenta recuperar imágenes faltantes} {--import-new : Importa nuevos registros que no existan} {--dry-run : Muestra acciones sin escribir}';
    protected $description = 'Detecta nuevos guides en WordPress y opcionalmente los importa; además recupera imágenes faltantes.';

    public function handle(): int
    {
        if (! $this->testConnection()) {
            $this->error('No conecta a la BD de WordPress');
            return 1;
        }

        $dry = $this->option('dry-run');
        $doImport = $this->option('import-new');
        $fixImages = $this->option('fix-images');

        $this->info('Buscando guías (dlm_download) en WordPress...');

        $wpGuides = DB::connection('wordpress')->select("
            SELECT p.ID as wp_id, p.post_title, p.post_content, p.post_excerpt, p.post_status, p.post_date
            FROM wp_posts p
            WHERE p.post_type = 'dlm_download' AND p.post_status = 'publish'
        ");

        $existing = Material::pluck('wp_id')->filter()->all();
        $existingMap = array_fill_keys($existing, true);

        $new = [];
        foreach ($wpGuides as $row) {
            if (!isset($existingMap[$row->wp_id])) {
                $new[] = $row;
            }
        }

        $this->line('Total WP: '.count($wpGuides).' | Existentes: '.count($existing).' | Nuevos: '.count($new));

        if ($new) {
            $this->info('Nuevos pendientes:');
            foreach ($new as $n) {
                $this->line(' - ['.$n->wp_id.'] '. $n->post_title);
            }

            if ($doImport) {
                $this->newLine();
                $this->info('Importando nuevos...');
                foreach ($new as $n) {
                    $data = $this->buildGuideData($n);
                    if ($dry) {
                        $this->line('[DRY] Crearía: '.$data['title']);
                        continue;
                    }
                    Material::create($data);
                    $this->line('Creado: '.$data['title']);
                }
            } else {
                $this->comment('Usa --import-new para importarlos automáticamente.');
            }
        } else {
            $this->info('No hay guías nuevas.');
        }

        if ($fixImages) {
            $this->newLine();
            $this->info('Revisando imágenes faltantes...');
            $sinImagen = Material::whereNull('image')->orWhere('image','')->get();
            $this->line('Materiales sin imagen: '.$sinImagen->count());

            foreach ($sinImagen as $mat) {
                if (! $mat->wp_id) continue; // sólo WordPress
                $image = $this->findImageFor($mat->wp_id, $mat->title);
                if ($image) {
                    if ($dry) {
                        $this->line('[DRY] Asignaría imagen a '.$mat->id.' => '.$image);
                        continue;
                    }
                    $mat->update(['image' => $image]);
                    $this->line('Imagen asignada a '.$mat->id.' => '.$image);
                } else {
                    $this->warn('No se encontró imagen para '.$mat->id.' (WP '.$mat->wp_id.')');
                }
            }
        } else {
            $this->comment('Usa --fix-images para intentar recuperar imágenes faltantes.');
        }

        $this->info('Listo.');
        return 0;
    }

    private function testConnection(): bool
    {
        try { DB::connection('wordpress')->select('SELECT 1'); return true; } catch (\Exception $e) { return false; }
    }

    private function buildGuideData($row): array
    {
        $description = $row->post_content ?: $row->post_excerpt ?: '';
        $description = strip_tags($description);
        $publishedAt = null;
        if ($row->post_status === 'publish' && $row->post_date) {
            try { $publishedAt = Carbon::parse($row->post_date); } catch (\Exception $e) { $publishedAt = now(); }
        } else { $publishedAt = now(); }

        // Imagen tentativa inicial: se intentará en fix-images si fue null
        $image = $this->findImageFor($row->wp_id, $row->post_title);

        // PDF placeholder (mismo fallback que import principal)
        $pdf = "https://huesped.org.ar/descargas/{$row->wp_id}/";

        return [
            'wp_id' => $row->wp_id,
            'title' => $row->post_title,
            'description' => $description,
            'image' => $image,
            'pdf_file' => $pdf,
            'status' => \App\Enums\Status::Published,
        ];
    }

    private function findImageFor(int $wpId, string $title): ?string
    {
        // Primero: thumbnail directo
        $thumb = DB::connection('wordpress')->select("SELECT meta_value FROM wp_postmeta WHERE post_id = ? AND meta_key = '_thumbnail_id' LIMIT 1", [$wpId]);
        if (!empty($thumb)) {
            $img = $this->attachmentUrl($thumb[0]->meta_value);
            if ($img) return $img;
        }

        // Hijos attachments
        $child = DB::connection('wordpress')->select("SELECT guid FROM wp_posts WHERE post_parent = ? AND post_type = 'attachment' AND post_mime_type LIKE 'image/%' ORDER BY ID DESC LIMIT 1", [$wpId]);
        if (!empty($child)) return $child[0]->guid;

        // Palabras clave del título
        $clean = preg_replace('/[^a-zA-Z0-9\s]/','', $title);
        $words = explode(' ', strtolower($clean));
        foreach ($words as $w) {
            if (strlen($w) < 4) continue;
            $res = DB::connection('wordpress')->select("SELECT guid FROM wp_posts WHERE post_type='attachment' AND post_mime_type LIKE 'image/%' AND LOWER(post_title) LIKE ? ORDER BY ID DESC LIMIT 1", ['%'.$w.'%']);
            if (!empty($res)) return $res[0]->guid;
        }

        return null;
    }

    private function attachmentUrl($attachmentId): ?string
    {
        if (!$attachmentId) return null;
        try {
            $att = DB::connection('wordpress')->select("SELECT guid FROM wp_posts WHERE ID = ? AND post_type='attachment' LIMIT 1", [$attachmentId]);
            if (!empty($att)) return $att[0]->guid;
        } catch (\Exception $e) { }
        return null;
    }
}
