<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Material;
use DOMDocument;
use DOMXPath;
use Carbon\Carbon;

class ScrapeMaterialsCommand extends Command
{
    protected $signature = 'scrape:materials {--test} {--all} {--clean} {--force}';
    protected $description = 'Scrape materials from huesped.org.ar/materiales/guias/';

    private $baseUrl = 'https://huesped.org.ar/materiales/guias/';
    private $downloadBaseUrl = 'https://huesped.org.ar/descargas/';

    public function handle()
    {
        $this->info('Iniciando scraping de materiales desde: ' . $this->baseUrl);

        if ($this->option('clean')) {
            $this->info('Limpiando materiales existentes...');
            // Usar delete() en lugar de truncate() para evitar problemas con claves foráneas
            Material::query()->delete();
        }

        $materials = $this->scrapeMaterials();
        
        if (empty($materials)) {
            $this->warn('No se encontraron materiales para procesar.');
            return 1;
        }

        $this->info('Procesando ' . count($materials) . ' materiales...');
        
        $saved = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($materials as $materialData) {
            if (!$materialData) {
                $errors++;
                continue;
            }

            try {
                if ($this->option('test')) {
                    $this->line('TEST MODE - Material: ' . $materialData['title']);
                    $this->line('  → Description: ' . substr($materialData['description'], 0, 100) . '...');
                    $this->line('  → PDF File: ' . ($materialData['pdf_file'] ?: 'No encontrado'));
                    $this->line('  → WP ID: ' . ($materialData['wp_id'] ?: 'No encontrado'));
                    $this->line('  → Image: ' . ($materialData['image'] ?: 'No encontrada'));
                    $this->line('  → Published: ' . ($materialData['published_at'] ?: 'No encontrada'));
                    $this->line('---');
                    $saved++;
                    continue;
                }

                // Verificar si ya existe (por título o wp_id)
                $existing = Material::where('title', $materialData['title'])
                    ->orWhere('wp_id', $materialData['wp_id'])
                    ->first();

                if ($existing && !$this->option('force')) {
                    $this->line('Saltando material existente: ' . $materialData['title']);
                    $skipped++;
                    continue;
                }

                if ($existing && $this->option('force')) {
                    $existing->update($materialData);
                    $this->line('Actualizado: ' . $materialData['title']);
                } else {
                    Material::create($materialData);
                    $this->line('Guardado: ' . $materialData['title']);
                }
                
                $saved++;
                
            } catch (\Exception $e) {
                $this->error('Error al guardar material: ' . $e->getMessage());
                $errors++;
            }
        }

        $this->info("\nResumen:");
        $this->info("Materiales procesados: $saved");
        $this->info("Materiales saltados: $skipped");
        $this->info("Errores: $errors");

        return 0;
    }

    protected function scrapeMaterials()
    {
        $allMaterials = [];
        $page = 1;
        $maxPages = 50; // Extraer todas las páginas por defecto
        
        do {
            $url = $this->baseUrl . ($page > 1 ? "page/$page/" : '');
            $this->info("Scraping página $page: $url");
            
            $html = $this->fetchPage($url);
            if (!$html) {
                $this->warn("No se pudo obtener contenido de la página $page");
                break;
            }
            
            $materials = $this->extractMaterialsFromPage($html);
            
            if (empty($materials)) {
                $this->info("No se encontraron más materiales en la página $page");
                break;
            }
            
            $allMaterials = array_merge($allMaterials, $materials);
            $this->info("Encontrados " . count($materials) . " materiales en la página $page");
            
            $page++;
            
            // Pausa entre requests
            sleep(1);
            
        } while ($page <= $maxPages);
        
        $this->info("Total de materiales encontrados: " . count($allMaterials));
        return $allMaterials;
    }

    protected function extractMaterialsFromPage($html)
    {
        $dom = new DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new DOMXPath($dom);
        
        // Intentar diferentes selectores para encontrar los materiales
        $selectors = [
            '//div[contains(@class, "post-listing") and contains(@class, "archive-box")]',
            '//article[contains(@class, "post")]',
            '//div[contains(@class, "post")]',
            '//div[contains(@class, "entry")]',
            '//div[contains(@class, "material")]',
            '//div[contains(@class, "item")]',
            '//div[contains(@class, "content")]//div[contains(@class, "post")]',
            '//main//article',
            '//main//div[contains(@class, "post")]'
        ];
        
        $materials = [];
        
        foreach ($selectors as $selector) {
            $this->line("Probando selector: {$selector}");
            $materialNodes = $xpath->query($selector);
            $this->line("Encontrados {$materialNodes->length} nodos con este selector");
            
            if ($materialNodes->length > 0) {
                foreach ($materialNodes as $node) {
                    $materialData = $this->extractMaterialData($node, $xpath);
                    if ($materialData) {
                        $materials[] = $materialData;
                    }
                }
                
                if (!empty($materials)) {
                    $this->info("Usando selector exitoso: {$selector}");
                    break;
                }
            }
        }
        
        // Si no encontramos nada, mostrar información de debug
        if (empty($materials)) {
            $this->warn('No se encontraron materiales con ningún selector. Mostrando estructura de la página...');
            $allDivs = $xpath->query('//div[@class]');
            $this->line("Total de divs con clase: {$allDivs->length}");
            
            // Mostrar las primeras 10 clases encontradas
            $classes = [];
            foreach ($allDivs as $i => $div) {
                if ($i >= 10) break;
                $class = $div->getAttribute('class');
                if ($class && !in_array($class, $classes)) {
                    $classes[] = $class;
                    $this->line("Clase encontrada: {$class}");
                }
            }
        }
        
        return $materials;
    }

    protected function extractMaterialData($node, $xpath)
    {
        try {
            // Intentar diferentes contenedores para los datos del material
            $containerSelectors = [
                './/div[contains(@class, "item-list")]',
                './/div[contains(@class, "entry-content")]',
                './/div[contains(@class, "post-content")]',
                './/div[contains(@class, "content")]',
                '.', // El nodo actual como último recurso
            ];
            
            $itemListNode = null;
            foreach ($containerSelectors as $selector) {
                $itemListNode = $xpath->query($selector, $node)->item(0);
                if ($itemListNode) {
                    $this->line("  → Usando contenedor: {$selector}");
                    break;
                }
            }
            
            if (!$itemListNode) {
                $this->warn('No se encontró contenedor válido en el nodo');
                return null;
            }
            
            // Extraer título con selectores más amplios
            $titleSelectors = [
                './/h1', './/h2', './/h3', './/h4', './/h5', './/h6',
                './/a[contains(@class, "title")]',
                './/div[contains(@class, "title")]',
                './/span[contains(@class, "title")]',
                './/a[contains(@href, "/materiales/")]',
                './/a[not(contains(@href, "#"))]'
            ];
            
            $title = null;
            foreach ($titleSelectors as $selector) {
                $titleNode = $xpath->query($selector, $itemListNode)->item(0);
                if ($titleNode) {
                    $candidateTitle = trim($titleNode->textContent);
                    // Filtrar títulos muy cortos o que parezcan navegación
                    if (strlen($candidateTitle) > 10 && !in_array(strtolower($candidateTitle), ['leer más', 'descargar', 'ver más', 'continuar'])) {
                        $title = $candidateTitle;
                        $this->line("  → Título encontrado con selector {$selector}: {$title}");
                        break;
                    }
                }
            }

            if (!$title) {
                $this->warn('No se encontró título válido en el contenedor');
                return null;
            }

            // Extraer descripción desde item-list
            $descriptionNodes = $xpath->query('.//p | .//div[contains(@class, "excerpt") or contains(@class, "description")]', $itemListNode);
            $description = '';

            foreach ($descriptionNodes as $descNode) {
                $text = trim($descNode->textContent);
                if (!empty($text) && !preg_match('/^(Sep|Oct|Nov|Dic|Ene|Feb|Mar|Abr|May|Jun|Jul|Ago)\s+\d{4}$/i', $text)) {
                    $description .= $text . ' ';
                }
            }

            $description = trim($description);
            // Limpiar la descripción removiendo "Descargar »" y otros elementos no deseados
            $description = str_replace(['Descargar »', 'Descargar', 'Leer más'], '', $description);
            $description = trim($description);

            // Extraer fecha desde item-list
            $dateText = null;
            $dateNodes = $xpath->query('.//*[contains(text(), "2024") or contains(text(), "2023") or contains(text(), "2022") or contains(text(), "2021")]', $itemListNode);

            foreach ($dateNodes as $dateNode) {
                $text = trim($dateNode->textContent);
                if (preg_match('/(Sep|Oct|Nov|Dic|Ene|Feb|Mar|Abr|May|Jun|Jul|Ago)\s+\d{4}/i', $text, $matches)) {
                    $dateText = $matches[0];
                    break;
                }
            }

            $publishedAt = $this->parseDate($dateText);

            // Extraer imagen desde item-list
            $image = null;
            $imageNodes = $xpath->query('.//img', $itemListNode);

            foreach ($imageNodes as $imageNode) {
                $src = $imageNode->getAttribute('src');
                if ($src) {
                    // Filtrar avatares de Gravatar y otras imágenes no deseadas
                    if (
                        strpos($src, 'gravatar.com') !== false ||
                        strpos($src, 'avatar') !== false ||
                        strpos($src, 'profile') !== false
                    ) {
                        continue; // Saltar avatares
                    }

                    // Priorizar imágenes más grandes
                    if (strpos($src, '-150x150') !== false) {
                        // Intentar obtener la versión original removiendo el sufijo de tamaño
                        $originalSrc = preg_replace('/-\d+x\d+/', '', $src);
                        $image = $originalSrc;
                        break; // Usar la primera imagen válida encontrada
                    } elseif (strpos($src, 'thumbnail') === false && strpos($src, '-thumb') === false) {
                        // Usar imágenes que no sean thumbnails
                        $image = $src;
                        break;
                    } elseif (!$image) {
                        // Como último recurso, usar cualquier imagen disponible (que no sea avatar)
                        $image = $src;
                    }
                }
            }

            // Si encontramos una imagen, asegurar que sea una URL completa
            if ($image && !str_starts_with($image, 'http')) {
                $image = 'https://huesped.org.ar' . $image;
            }

            // Buscar el more-link dentro del item-list
            $moreLinkNode = $xpath->query('.//a[contains(@class, "more-link") or contains(text(), "Leer más") or contains(text(), "Ver más")]', $itemListNode)->item(0);
            
            $downloadLink = null;
            $wpId = null;

            if ($moreLinkNode) {
                $moreLinkUrl = $moreLinkNode->getAttribute('href');
                
                // Asegurar que sea una URL completa
                if (!str_starts_with($moreLinkUrl, 'http')) {
                    $moreLinkUrl = 'https://huesped.org.ar' . $moreLinkUrl;
                }
                
                $this->line("  → More-link encontrado: {$moreLinkUrl}");
                
                try {
                    // Obtener la URL final después de redirecciones
                    $finalUrl = $this->getFinalRedirectUrl($moreLinkUrl);
                    $this->line("  → URL final después de redirección: {$finalUrl}");
                    
                    // Extraer el ID de la URL intermedia (formato: https://huesped.org.ar/descarga/?id=23960)
                    if (preg_match('/[?&]id=(\d+)/', $finalUrl, $matches)) {
                        $wpId = $matches[1];
                        // Construir la URL directa al PDF usando el formato especificado
                        $downloadLink = "https://huesped.org.ar/descargas/{$wpId}";
                        $this->line("  → ID extraído: {$wpId}");
                        $this->line("  → URL directa de descarga: {$downloadLink}");
                    } else {
                        $this->warn("  → No se pudo extraer el ID de la URL: {$finalUrl}");
                        // Como fallback, intentar extraer ID de otras partes de la URL
                        if (preg_match('/\/(\d+)\/?$/', $finalUrl, $matches)) {
                            $wpId = $matches[1];
                            $downloadLink = "https://huesped.org.ar/descargas/{$wpId}";
                            $this->line("  → ID extraído como fallback: {$wpId}");
                        }
                    }
                } catch (\Exception $e) {
                    $this->warn("Error al procesar more-link: " . $e->getMessage());
                }
            } else {
                $this->warn('No se encontró more-link en item-list');
                // Como último recurso, buscar cualquier enlace que pueda ser el more-link
                $fallbackLinkNode = $xpath->query('.//a[contains(@href, "/materiales/guias/")]', $itemListNode)->item(0);
                if ($fallbackLinkNode) {
                    $fallbackUrl = $fallbackLinkNode->getAttribute('href');
                    if (!str_starts_with($fallbackUrl, 'http')) {
                        $fallbackUrl = 'https://huesped.org.ar' . $fallbackUrl;
                    }
                    $this->line('  → Usando enlace alternativo como more-link: ' . $fallbackUrl);
                    
                    try {
                        $finalUrl = $this->getFinalRedirectUrl($fallbackUrl);
                        if (preg_match('/[?&]id=(\d+)/', $finalUrl, $matches)) {
                            $wpId = $matches[1];
                            $downloadLink = "https://huesped.org.ar/descargas/{$wpId}";
                            $this->line("  → ID extraído del enlace alternativo: {$wpId}");
                        }
                    } catch (\Exception $e) {
                        $this->warn("Error al procesar enlace alternativo: " . $e->getMessage());
                    }
                }
            }

            // Extraer ID del post de WordPress desde el enlace de descarga
            if ($downloadLink) {
                if (preg_match('/descargas\/(\d+)/', $downloadLink, $matches)) {
                    $wpId = $matches[1];
                } elseif (preg_match('/[?&]id=(\d+)/', $downloadLink, $matches)) {
                    $wpId = $matches[1];
                }
            } else {
                // Si no hay enlace de descarga, usar la página del material como fallback
                if ($moreLinkNode) {
                    $materialPageUrl = $moreLinkNode->getAttribute('href');
                    $downloadLink = $materialPageUrl;
                    // Intentar extraer un ID de la URL de la página
                    if (preg_match('/\/(\d+)\/?$/', $materialPageUrl, $matches)) {
                        $wpId = $matches[1];
                    } elseif (preg_match('/[?&]p=(\d+)/', $materialPageUrl, $matches)) {
                        $wpId = $matches[1];
                    } elseif (preg_match('/[?&]page_id=(\d+)/', $materialPageUrl, $matches)) {
                        $wpId = $matches[1];
                    } else {
                        // Generar un ID basado en el hash de la URL
                        $wpId = abs(crc32($materialPageUrl)) % 100000;
                    }
                }
            }

            // Solo retornar si tenemos al menos título, descripción y pdf_file
            if (empty($title) || empty($description) || empty($downloadLink)) {
                $this->warn("  → Material incompleto - Título: {$title}, Descripción: " . (empty($description) ? 'vacía' : 'presente') . ", PDF: " . (empty($downloadLink) ? 'no encontrado' : 'presente'));
                return null;
            }

            return [
                'title' => $title,
                'description' => $description,
                'image' => $image,
                'published_at' => $publishedAt,
                'pdf_file' => $downloadLink, // Campo requerido en la base de datos
                'wp_id' => $wpId
            ];
        } catch (\Exception $e) {
            $this->warn("Error al extraer datos del material: " . $e->getMessage());
            return null;
        }
    }

    private function getFinalRedirectUrl($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
        
        $response = curl_exec($ch);
        $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch);
        
        return $finalUrl;
    }

    private function fetchPage($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
        
        $html = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200 || !$html) {
            return false;
        }
        
        return $html;
    }

    private function parseDate($dateText)
    {
        if (!$dateText) {
            // Si no hay fecha, usar la fecha actual como fallback
            return Carbon::now()->format('Y-m-d H:i:s');
        }

        $months = [
            'Ene' => 'Jan', 'Feb' => 'Feb', 'Mar' => 'Mar', 'Abr' => 'Apr',
            'May' => 'May', 'Jun' => 'Jun', 'Jul' => 'Jul', 'Ago' => 'Aug',
            'Sep' => 'Sep', 'Oct' => 'Oct', 'Nov' => 'Nov', 'Dic' => 'Dec'
        ];

        foreach ($months as $spanish => $english) {
            $dateText = str_replace($spanish, $english, $dateText);
        }

        try {
            return Carbon::parse($dateText)->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            // Si hay error al parsear, usar fecha actual como fallback
            return Carbon::now()->format('Y-m-d H:i:s');
        }
    }
}
