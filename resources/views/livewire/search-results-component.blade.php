<div class="mx-auto md:px-2 py-4 sm:py-8">
    <div class="mb-4 sm:mb-8">
        <div class="max-w-2xl mx-auto">
            <div class="relative flex items-center px-3 sm:px-4 border border-gray-300 rounded-lg bg-white">
                <svg class="w-5 h-5 text-neutral-400 shrink-0" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" x2="16.65" y1="21" y2="16.65"></line></svg>
                <input type="text" 
                    id="search-results-input"
                    wire:model.live.debounce.300ms="search"
                    class="flex px-3 py-4 w-full text-md bg-transparent border-0 outline-none focus:outline-none focus:ring-0 placeholder:text-neutral-400 disabled:cursor-not-allowed disabled:opacity-50" 
                    placeholder="Buscar..." 
                    autocomplete="off" 
                    autocorrect="off" 
                    @keydown.enter="if ($event.target.value.length >= 3) { window.location.href = '{{ route('search.index') }}?s=' + encodeURIComponent($event.target.value); }"
                    @input="if ($event.target.value.length >= 3) { 
                        const url = new URL(window.location);
                        url.searchParams.set('s', $event.target.value);
                        window.history.replaceState({}, '', url);
                    }"
                    spellcheck="false">
                <div wire:loading class="absolute right-4">
                    <svg class="animate-spin h-5 w-5 text-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Mensaje cuando no hay resultados -->
    @if(empty($suggestions['results']) || count($suggestions['results']) === 0)
        @if(strlen($search) >= 3)
            <div class="text-center py-8 sm:py-16 max-w-2xl mx-auto">
                <svg class="mx-auto h-12 w-12 sm:h-16 sm:w-16 text-gray-400 max-w-[100px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="mt-3 sm:mt-4 text-base sm:text-lg font-medium text-gray-900">No se encontraron resultados</h3>
                <p class="mt-1 sm:mt-2 text-sm sm:text-base text-gray-500">No se encontraron resultados para "{{ $search }}".</p>
            </div>
        @else
            <div class="text-center py-8 sm:py-16 max-w-2xl mx-auto">
                <p class="text-sm sm:text-base text-gray-500">Ingresa al menos 3 caracteres para buscar</p>
            </div>
        @endif
    @else
        <div class="grid grid-cols-1 gap-4 sm:gap-8 max-w-4xl mx-auto">
            @foreach($suggestions['results'] as $index => $items)
                @if(isset($suggestions['categories'][$index]) && is_array($items) && count($items) > 0)
                    <div class="mb-4">
                        <h2 class="text-xl sm:text-2xl font-bold text-gray-900 mb-2 sm:mb-4 px-2 sm:px-0">{{ $suggestions['categories'][$index] }}</h2>
                        
                        <div class="space-y-3 sm:space-y-4">
                            @foreach($items as $item)
                                <div class="bg-white hover:bg-gray-100 py-2 shadow-sm p-3 sm:p-5 hover:shadow-md transition-shadow duration-300 w-full">
                                    <a href="{{ url($item['url']) }}" class="block">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                @if($item['type'] === 'blog')
                                                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-primary" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                                                @elseif($item['type'] === 'page')
                                                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-primary" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path></svg>
                                                @endif
                                            </div>
                                            <div class="ml-3 sm:ml-4 flex-grow">
                                                <h3 class="text-sm sm:text-md font-semibold text-gray-900 ">{!! $item['title'] !!}</h3>
                                                @if(isset($item['fragment']))
                                                    <div class="mt-1 sm:mt-2 text-xs sm:text-sm text-gray-600 ">{!! $item['fragment'] !!}</div>
                                                @endif
                                                <div class="mt-1 sm:mt-2 text-xs text-secondary overflow-hidden hidden md:block">
                                                    <span class="block max-w-full break-words">{{ url($item['url']) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    @endif
</div>

<script>
    // Escuchar cambios en la URL para actualizar el componente Livewire
    window.addEventListener('popstate', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const searchParam = urlParams.get('s');
        if (searchParam !== null) {
            @this.set('search', searchParam);
        }
    });
</script>
