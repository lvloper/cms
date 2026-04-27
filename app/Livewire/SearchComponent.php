<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Page;
use App\Models\Blog;
use Livewire\WithPagination;
use Livewire\Attributes\On;

class SearchComponent extends Component
{
    use WithPagination;

    public $search = '';
    public $showResults = false;
    public $searchIn = null;
    public $direction = 'bottom';
    public $suggestions = [
        'results' => [],
        'categories' => []
    ];
    public $isFullPage = false;
    public $resultsLimit = 5; // Límite por defecto para dropdown
    public $autoSearch = false; // Para forzar búsqueda automática

    public function mount($isFullPage = false, $autoSearch = false, $initialSearch = '')
    {
        $this->isFullPage = $isFullPage;
        $this->autoSearch = $autoSearch;
        
        // Si es página completa de resultados, ajustar el límite y obtener la búsqueda de la URL
        if ($this->isFullPage) {
            $this->resultsLimit = 20; // Aumentar el límite para la página completa
            $this->search = request()->get('s', $initialSearch);
            $this->showResults = true;
            
            if (strlen($this->search) >= 3) {
                $this->search();
            }
        }
        
        // Si autoSearch está activado y hay un término inicial
        if ($this->autoSearch && $initialSearch) {
            $this->search = $initialSearch;
            $this->showResults = true;
            
            if (strlen($this->search) >= 3) {
                $this->search();
            }
        }
    }
    
    public function updated($property)
    {
        if ($property === 'search') {
            $this->search();
            
            // Si estamos en la página completa, actualizar el searchTerm en la sesión
            if ($this->isFullPage) {
                session()->flash('searchTerm', $this->search);
            }
        }
    }

    public function search()
    {
        if (strlen($this->search) >= 3) {
            \Log::debug('Iniciando búsqueda con: ' . $this->search);
            
            // Buscar páginas
            $pagesQuery = Page::where('blocks', 'like', '%' . $this->search . '%')
                ->whereHas('route', function($query) {
                    $query->where('status', 'published');
                    
                    if ($this->searchIn) {
                        $route = \App\Models\Route::find($this->searchIn);
                        if ($route) {
                            // Obtener todos los IDs de rutas descendientes incluyendo la ruta actual
                            $routeIds = $route->getDescendantIds();
                            $query->whereIn('id', $routeIds);
                        } else {
                            // Si la ruta no existe, no devolver resultados
                            $query->where('id', 0);
                        }
                    }
                })
                ->with('route')
                ->limit($this->resultsLimit);

            $pages = $pagesQuery->get();
            \Log::debug('Páginas encontradas: ' . $pages->count());

            // Solo buscar blogs si no hay searchIn
            $blogs = collect([]);
            if (!$this->searchIn) {
                $blogs = Blog::where(function($query) {
                        $query->where('description', 'like', '%' . $this->search . '%')
                              ->orWhere('content', 'like', '%' . $this->search . '%');
                    })
                    ->whereHas('route', function($query) {
                        $query->where('status', 'published');
                    })
                    ->where('published_at', '<=', now())
                    ->with('route')
                    ->limit($this->resultsLimit)
                    ->get();
                \Log::debug('Blogs encontrados: ' . $blogs->count());
            }

            // Inicializar la estructura de suggestions
            $this->suggestions = [
                'results' => [],
                'categories' => []
            ];

            $allPageResults = [];

            // Procesar páginas y encontrar fragmentos relevantes
            foreach ($pages as $page) {
                try {
                    $blocks = json_decode($page->blocks, true, 512, JSON_INVALID_UTF8_SUBSTITUTE);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        \Log::error('Error decodificando JSON para página ' . $page->id . ': ' . json_last_error_msg());
                        continue;
                    }

                    foreach ($blocks as $block) {
                        if (!isset($block['data']) || !is_array($block['data'])) {
                            continue;
                        }

                        $found = false;
                        $fragment = '';
                        $blockTitle = $block['data']['blockTitle'] ?? null;
                        
                        // Buscar en el título del bloque
                        if (isset($block['data']['title'])) {
                            $title = mb_convert_encoding($block['data']['title'], 'UTF-8', 'UTF-8');
                            if (mb_stripos($title, $this->search) !== false) {
                                $fragment = $this->highlightSearchInText($title, $this->search);
                                $found = true;
                            }
                        }
                        
                        // Buscar en todo el contenido del bloque
                        if (!$found) {
                            $matches = $this->searchInArray($block['data'], $this->search);
                            if (!empty($matches)) {
                                $found = true;
                                // Extraer fragmento relevante
                                $fragment = '';
                                if (!empty($matches)) {
                                    $fragment = $matches[0];
                                    if (mb_strlen($fragment) > 150) {
                                        $fragment = mb_substr($fragment, 0, 150) . '...';
                                    }
                                    // Aplicamos la limpieza y el resaltado
                                    $fragment = $this->highlightSearchInText($fragment, $this->search);
                                }
                            }
                        }
                        
                        if ($found) {
                            $allPageResults[] = [
                                'title' => $this->highlightSearchInText($blockTitle ?? $page->route->title, $this->search),
                                'value' => 'page_' . $page->id,
                                'url' => $page->route->getFullPath(),
                                'fragment' => $fragment,
                                'type' => 'page'
                            ];
                        }
                    }
                } catch (\Exception $e) {
                    \Log::error('Error procesando página ' . $page->id . ': ' . $e->getMessage());
                    continue;
                }
            }
            
            // Agregar todos los resultados de páginas bajo una sola categoría "Sugerencias"
            if (!empty($allPageResults)) {
                $this->suggestions['categories'][0] = 'Sugerencias';
                $this->suggestions['results'][0] = $allPageResults;
                \Log::debug('Total de sugerencias de páginas: ' . count($allPageResults));
            }

            // Procesar blogs
            if (!empty($blogs)) {
                $blogResults = [];
                foreach ($blogs as $blog) {
                    // Limpiamos la descripción antes de procesarla
                    $description = $this->cleanText($blog->description);
                    if (mb_strlen($description) > 150) {
                        $description = mb_substr($description, 0, 150) . '...';
                    }
                    $blogResults[] = [
                        'title' => $this->highlightSearchInText($blog->route->title, $this->search),
                        'value' => 'blog_' . $blog->id,
                        'url' => $blog->route->getFullPath(),
                        'fragment' => $this->highlightSearchInText($description, $this->search),
                        'type' => 'blog'
                    ];
                }

                if (!empty($blogResults)) {
                    $this->suggestions['categories'][1] = 'Noticias';
                    $this->suggestions['results'][1] = $blogResults;
                    \Log::debug('Total de resultados de blogs: ' . count($blogResults));
                }
            }

            // Verificar la estructura final
            \Log::debug('Estructura final de suggestions:', [
                'categories_count' => count($this->suggestions['categories']),
                'results_count' => count($this->suggestions['results']),
                'categories' => $this->suggestions['categories']
            ]);

            // Mostrar resultados incluso si están vacíos, para poder mostrar el mensaje "No se encontraron resultados"
            $this->showResults = true;
            \Log::debug('showResults: ' . ($this->showResults ? 'true' : 'false'));
        } else {
            // Si la búsqueda es menor a 3 caracteres, no mostrar resultados
            $this->showResults = false;
            $this->suggestions = [
                'results' => [],
                'categories' => []
            ];
        }
    }

    private function cleanText($text)
    {
        if (is_array($text)) {
            $text = $this->extractPlainTextFromArray($text);
        }

        $text = (string) ($text ?? '');

        // Remover contenido entre llaves
        $text = preg_replace('/\{[^}]*\}/', '', $text);
        // Eliminar todas las etiquetas HTML
        $text = strip_tags($text);
        // Convertir entidades HTML a sus caracteres correspondientes
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        // Eliminar caracteres especiales y dejar solo alfanuméricos, espacios y puntuación básica
        $text = preg_replace('/[^\p{L}\p{N}\s\.\,\:\;\-\_\?\¿\!\¡]/u', '', $text);
        // Eliminar espacios múltiples
        $text = preg_replace('/\s+/', ' ', $text);
        return trim($text);
    }

    private function extractPlainTextFromArray(array $value): string
    {
        $text = '';

        array_walk_recursive($value, function ($item, $key) use (&$text) {
            if ($key === 'text' && is_scalar($item)) {
                $text .= ' ' . $item;
            }
        });

        if ($text !== '') {
            return $text;
        }

        return json_encode($value, JSON_UNESCAPED_UNICODE) ?: '';
    }

    private function extractTextFromBlock($textBlock)
    {
        $text = '';
        try {
            if (isset($textBlock['content'])) {
                foreach ($textBlock['content'] as $content) {
                    if ($content['type'] === 'paragraph') {
                        if (isset($content['content'])) {
                            foreach ($content['content'] as $item) {
                                if (isset($item['text'])) {
                                    $text .= $item['text'] . ' ';
                                }
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::error('Error extrayendo texto del bloque: ' . $e->getMessage());
        }
        return $this->cleanText($text);
    }

    private function highlightSearchInText($text, $search)
    {
        // Primero limpiamos el texto
        $text = $this->cleanText($text);
        // Luego resaltamos la palabra buscada
        $pattern = '/' . preg_quote($search, '/') . '/i';
        return preg_replace($pattern, '<strong>$0</strong>', $text);
    }

    private function searchInArray($array, $search)
    {
        $results = [];
        
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $results = array_merge($results, $this->searchInArray($value, $search));
            } elseif (is_string($value)) {
                $cleanValue = $this->cleanText($value);
                if (mb_stripos($cleanValue, $search) !== false) {
                    $results[] = $cleanValue;
                }
            }
        }
        
        return $results;
    }

    public function render()
    {
        // Seleccionar la vista según si es página completa o dropdown
        if ($this->isFullPage) {
            return view('livewire.search-results-component');
        } else {
            return view('livewire.search-component');
        }
    }
}
