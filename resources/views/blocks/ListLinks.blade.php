<x-block class="py-8 px-4 md:px-6">
    <div class="container max-w-7xl">
        <!-- Masonry CSS -->
        <style>
            .masonry {
                margin: 0 auto;
            }

            .masonry-item {
                width: 100%;
                margin-bottom: 1.5rem;
            }

            @media (min-width: 768px) {
                .masonry-item {
                    width: calc(50% - 1rem);
                }
            }

            @media (min-width: 1280px) {
                .masonry-item {
                    width: calc(33.333% - 1rem);
                }
            }
        </style>

        <!-- Masonry Container -->
        <div class="masonry" id="masonry-grid" x-data="{
            initMasonry() {
                // Inicializar Masonry después de que todas las imágenes se hayan cargado
                if (typeof imagesLoaded !== 'undefined' && typeof Masonry !== 'undefined') {
                    imagesLoaded('#masonry-grid', function() {
                        new Masonry('#masonry-grid', {
                            itemSelector: '.masonry-item',
                            columnWidth: '.masonry-item',
                            percentPosition: true,
                            gutter: 24,
                            transitionDuration: '0.4s'
                        });
                    });
                }
            }
        }" x-init="initMasonry()">
            @foreach ($links as $link)
            @php
            $routable = \App\Models\Route::find($link['url']['route_id']);
            $indexItems = [];
            if($link['show_index'] && $routable) {
            $indexItems = $routable->getIndex();
            }
            @endphp

            <div class="masonry-item">
                <div
                    class="bg-white rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200 border border-gray-100 overflow-hidden h-full">
                    <div class="border-b border-gray-100 bg-gray-50 px-4 py-3">
                        <x-link :attrs="$link['url']"
                            class="text-gray-800 hover:text-primary font-bold text-base leading-tight transition-colors duration-200 flex items-center">
                            <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                </path>
                            </svg>
                            {{ $routable->title }}
                        </x-link>
                    </div>

                    @if($link['show_index'] && count($indexItems) > 0)
                    <div class="divide-y divide-gray-100 text-sm">
                        @foreach($indexItems as $indexItem)
                        <x-link :attrs="$link['url']" :anchor="$indexItem['id']"
                            class="block px-5 py-3 text-gray-700 hover:bg-blue-50 hover:text-primary transition-colors duration-150 flex items-start group">
                            <span
                                class="w-1.5 h-1.5 rounded-full bg-gray-300 mt-2 mr-3 flex-shrink-0 group-hover:bg-primary"></span>
                            <span class="flex-1">{{ $indexItem['title'] }}</span>
                            <svg class="w-4 h-4 text-gray-400 opacity-0 group-hover:opacity-100 transition-opacity"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                                </path>
                            </svg>
                        </x-link>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        <!-- Masonry CDN -->
        @pushOnce('styles', 'masonry')
        <script src="https://unpkg.com/masonry-layout@4.2.2/dist/masonry.pkgd.min.js"></script>
        <script src="https://unpkg.com/imagesloaded@5.0.0/imagesloaded.pkgd.min.js"></script>
        <script defer src="https://unpkg.com/alpinejs-masonry@latest/dist/masonry.min.js"></script>
        @endPushOnce
    </div>
</x-block>