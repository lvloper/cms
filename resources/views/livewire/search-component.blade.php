<div class="flex justify-center w-full items-start relative" x-data="{
    open: true,
}" @click.outside="open = false" @click="open=true">
    <div class="flex overflow-hidden flex-col w-full h-full bg-white text-md">
        <form class="flex relative items-center px-3 border-b" action="{{ route('search.index') }}" method="get">
            <svg class="hidden mr-0 w-4 h-4 md:block text-neutral-400 shrink-0" xmlns="http://www.w3.org/2000/svg"
                width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" x2="16.65" y1="21" y2="16.65"></line>
            </svg>
            <input type="text" id="search-input" wire:model.live.debounce.300ms="search" name="s"
                class="flex px-2 py-3 w-full h-[52px] bg-transparent rounded-md border-0 outline-none text-md focus:outline-none focus:ring-0 focus:border-0 placeholder:text-neutral-400 disabled:cursor-not-allowed disabled:opacity-50"
                placeholder="Buscar..." autocomplete="off" autocorrect="off" @keydown.enter.prevent="
                open = true;
                if ($event.target.value.length > 3) {
                    window.location.href = '{{ route('search.index') }}?s=' + encodeURIComponent($event.target.value);
                }" @input="if ($event.target.value.length > 3 && $wire.isFullPage) {
                    const url = new URL(window.location);
                    url.searchParams.set('s', $event.target.value);
                    window.history.replaceState({}, '', url);
                }" spellcheck="false">
            <button type="submit"
                class="flex justify-center items-center p-2 rounded-full md:hidden text-primary hover:bg-gray-100">
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" x2="16.65" y1="21" y2="16.65"></line>
                </svg>
            </button>
            <div wire:loading class="absolute right-12 md:right-3">
                <svg class="w-4 h-4 animate-spin text-secondary" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
            </div>
        </form>

        @if($showResults)
        <div x-show="open" x-cloak
            class="h-[320px] md:h-auto flex items-center justify-center w-full sm:flex-none max-h-[320px] overflow-y-auto overflow-x-hidden z-50 w-full bg-white border-b-[3px] border-secondary {{ $direction === 'top' ? '-top-[320px] absolute' : 'top-0 relative' }}">
            <div class="pb-1">
                <!-- Depuración -->
                <div class="px-2 py-1 my-1 text-xs bg-gray-100" style="display: none;">
                    <span>Categorías: {{ count($suggestions['categories']) }}, Grupos de resultados: {{
                        count($suggestions['results']) }}</span>
                </div>

                <!-- Mensaje cuando no hay resultados -->
                @if(empty($suggestions['results']) || count($suggestions['results']) === 0)
                <div class="px-4 py-6 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400 max-w-[100px]" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No se encontraron resultados</h3>
                    <p class="mt-1 text-sm text-gray-500">No se encontraron resultados para "{{ $search }}".</p>
                </div>
                @else
                <!-- Iteración directa usando Blade -->
                @foreach($suggestions['results'] as $index => $items)
                @if(isset($suggestions['categories'][$index]) && is_array($items) && count($items) > 0)
                <div class="overflow-hidden px-1 text-gray-700">
                    <div class="px-2 py-1 my-1 text-xs font-medium text-neutral-500">{{
                        $suggestions['categories'][$index] }}</div>
                </div>

                @foreach($items as $item)
                <div class="px-1">
                    <a href="{{ url($item['url']) }}"
                        class="flex relative flex-col px-2 py-1.5 text-sm rounded-sm cursor-pointer outline-none select-none hover:bg-neutral-100">
                        <div class="flex items-center">
                            @if($item['type'] === 'blog')
                            <svg class="mr-2 w-4 h-4 text-neutral-500" xmlns="http://www.w3.org/2000/svg" width="24"
                                height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                            </svg>
                            @elseif($item['type'] === 'page')
                            <svg class="mr-2 w-4 h-4 text-neutral-500" xmlns="http://www.w3.org/2000/svg" width="24"
                                height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                                <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
                            </svg>
                            @endif
                            <span>{!! $item['title'] !!}</span>
                        </div>
                        @if(isset($item['fragment']))
                        <div class="pl-6 mt-1 text-xs text-neutral-500">{!! $item['fragment'] !!}</div>
                        @endif
                    </a>
                </div>
                @endforeach
                @endif
                @endforeach

                <!-- Botón para ver todos los resultados -->
                @if(strlen($search) >= 3)
                <div class="px-1 py-2 mt-2 border-t">
                    <a href="{{ route('search.index', ['s' => $search]) }}"
                        class="flex justify-center items-center px-4 py-2 w-full text-sm font-medium rounded text-primary hover:bg-neutral-100">
                        <svg class="mr-2 w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" x2="16.65" y1="21" y2="16.65"></line>
                        </svg>
                        Ver todos los resultados
                    </a>
                </div>
                @endif
                @endif
            </div>
        </div>
        @endif
    </div>
</div>
