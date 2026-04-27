@php
// Lógica para obtener las novedades según la configuración del bloque
$news = [];

// Determinar el tipo de contenido (por defecto 'latest' si no está definido)
$contentType = $content_type ?? 'latest';

// DEBUG: Descomentar la siguiente línea para ver qué variables están disponibles
// dd(get_defined_vars());

// Si está configurado para mostrar notas específicas
if($contentType === 'specific' && isset($items) && !empty($items)) {
    foreach($items as $item) {
        if($item['route'] && $item['route']['route_id'] ) {
            $route_id = $item['route']['route_id'];
            $route = \App\Models\Route::find($route_id);
            
            // Verificar que la ruta existe y tiene un modelo relacionado
            if($route && $route->routable) {
                $news[] = $route->routable;
            }
        }
    }
}
// Si está configurado para mostrar por tags
elseif($contentType === 'tags' && isset($selected_tags) && !empty($selected_tags)) {
    $tagSlugs = [];
    
    // Procesar los tags que pueden venir en formato JSON
    foreach($selected_tags as $tag) {
        if(is_string($tag)) {
            // Si es un string JSON, decodificarlo
            $decoded = json_decode($tag, true);
            if(json_last_error() === JSON_ERROR_NONE && isset($decoded['es'])) {
                $tagSlugs[] = $decoded['es'];
            } else {
                // Si no es JSON, usar el string directamente
                $tagSlugs[] = $tag;
            }
        } else {
            // Si no es string, convertir a string
            $tagSlugs[] = (string) $tag;
        }
    }
    
    $tagSlugs = array_filter($tagSlugs); // Remover valores vacíos
    
    // DEBUG: Descomentar para ver los tags procesados
    // dd(['original_tags' => $selected_tags, 'processed_tags' => $tagSlugs]);
    
    if(!empty($tagSlugs)) {
        $news = \App\Models\Blog::withAnyTags($tagSlugs)
            ->orderBy('published_at', 'desc')
            ->isPublished()
            ->take(20)
            ->get();
    }
}

// Si no hay configuración específica o está configurado para 'latest', mostrar las últimas novedades
if(empty($news)) {
    $news = \App\Models\Blog::orderBy('published_at', 'desc')->isPublished()->take(20)->get();
}
@endphp
<x-block>
    <div class="">
        <x-common.line-title title="{{ $title }}" size="xl" class="mb-0" color="primary" />

        <div class="pt-12 pb-12 bg-gray-3">
            <div class="container">
                <div class="relative">
                    <swiper-container class="swiper-carrousel-news" slides-per-view="2" space-between="8"
                        navigation-next-el=".{{ $id }}-swiper-button-next"
                        navigation-prev-el=".{{ $id }}-swiper-button-prev" breakpoints='{
                            "768": {
                                "slidesPerView": 2.3,
                                "spaceBetween": 20
                            },
                            "1024": {
                                "slidesPerView": 3.3,
                                "spaceBetween": 20
                            },
                            "1536": {  
                                "slidesPerView": {{ isset($layout) && $layout == ' hasIndex' ? '3.3' : '4.3'
                        }}, "spaceBetween" : 20 } }'>
                        @foreach ($news as $item)
                        <swiper-slide>
                            <x-blog.card :item="$item" />
                        </swiper-slide>
                        @endforeach
                    </swiper-container>

                    <div
                        class="{{ $id }}-swiper-button-prev absolute -left-8 lg:-left-16 top-1/2 transform -translate-y-1/2 swiper-custom-buttons">
                        <x-lucide-chevron-left class=" w-8 h-8 lg:w-16 lg:h-16 stroke-2 text-primary" />
                    </div>
                    <div
                        class="{{ $id }}-swiper-button-next absolute -right-8 lg:-right-16 top-1/2 transform -translate-y-1/2 swiper-custom-buttons">
                        <x-lucide-chevron-right class=" w-8 h-8 lg:w-16 lg:h-16 stroke-2 text-primary" />
                    </div>

                    {{-- <div class="text-center pt-6">
                        @isset($route)
                        <x-link :attrs="$route"
                            class="mt-6 inline-block text-white px-6 py-1 text-lg bg-secondary font-bold hover:bg-primary">
                        </x-link>
                        @endisset
                    </div> --}}
                </div>
            </div>
        </div>
    </div>
</x-block>