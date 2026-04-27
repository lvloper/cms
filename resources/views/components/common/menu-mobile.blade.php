@php
$menu = App\Models\Menu::query()->where('slug', 'header')->first();
@endphp
<header x-cloak class="overflow-hidden sticky top-0 z-30 w-full bg-gray-200 md:fixed lg:hidden" x-data="{ 
                open: false,
                activeAccordion: null,
                setActiveAccordion(id) {
                    this.activeAccordion = this.activeAccordion === id ? null : id;
                }
            }" @toggle-mobile-menu.window="open = !open">
    <div class="absolute top-0 left-0 w-full h-full rounded-full circle-animate bg-primary"></div>

    <div class="flex justify-between px-6 md:px-[7rem] items-center h-[80px] md:h-[60px] bg-white">
        <div class="xl:pl-6">
            <a href="/" class="block text-lg font-bold uppercase tracking-wide text-primary">
                {{ config_text('site-name', 'CMS Base') }}
            </a>
        </div>

        <div class="md:hidden">
            <button @click="open = !open" class="relative text-xl" x-ref="hamburgerButton">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    class="w-8 h-8" x-ref="hamburgerIcon">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16">
                    </path>
                </svg>
            </button>
        </div>
    </div>

    <!-- Menu Animation Wrapper -->
    <div class="fixed inset-0 bg-primary z-50 px-6 md:px-[7rem]" x-show="open">

        <div class="flex justify-between items-center h-[80px] md:h-[60px] mb-20">
            <div class="xl:pl-6">
                <a href="/" class="block text-lg font-bold uppercase tracking-wide text-white">
                    {{ config_text('site-name', 'CMS Base') }}
                </a>
            </div>

            <div class="md:hidden">
                <button @click="open = false" class="relative text-xl text-white" x-ref="exitButton">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        class="w-8 h-8" x-ref="hamburgerIcon">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Menu Items -->
        @if ($menu)
        <div class="overflow-y-auto max-h-[calc(100vh-250px)]">
            @foreach ($menu->items as $parentKey => $item)
            @php
            $route = \App\Models\Route::find($item['route']['route_id']);
            $hasChildren = isset($item['children']) && !empty($item['children']);
            @endphp
            <div x-data="{ id: '{{ $parentKey }}' }" class="parent-item-{{ $parentKey }} mb-4">
                <div class="flex justify-between items-center"
                    :class="{ 'text-white': activeAccordion != id, 'text-white': activeAccordion == id }">
                    <div class="flex-1">
                        <x-link
                            class="text-lg font-bold uppercase block w-full {{ $route && !$item['route']['anchor'] && $route->isActiveParent() ? 'text-gray-200' : 'text-white' }}"
                            :attrs="$item['route']" @click="$event.preventDefault(); setActiveAccordion(id)"
                            @click.shift="$event.stopPropagation()">
                            {{ $item['label'] }}
                        </x-link>
                    </div>
                    @if($hasChildren)
                    <button @click="setActiveAccordion(id)" class="p-2 text-white">
                        <svg class="w-5 h-5 duration-300 ease-out" :class="{ '-rotate-[45deg]': activeAccordion == id }"
                            xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6" />
                        </svg>
                    </button>
                    @endif
                </div>

                @if($hasChildren)
                <div x-show="activeAccordion == id" x-collapse x-cloak class="pl-4 mt-2 space-y-2">
                    @foreach ($item['children'] as $child)
                    @php
                    $childRoute = \App\Models\Route::find($child['route']['route_id']);
                    $hasAnchor = $child['route']['anchor'] ?? false;
                    @endphp
                    <x-link allowWireNavitage
                        class="block text-md transition-all duration-200 hover:text-gray-200 {{ $childRoute && $childRoute->isActive() && !$hasAnchor ? 'text-gray-200 font-medium' : 'text-white' }}"
                        :attrs="$child['route']">
                        {{ $child['label'] }}
                    </x-link>
                    @endforeach
                </div>
                @endif
            </div>
            @endforeach
        </div>
        @endif

        <!-- Bottom content fixed at the bottom -->
        <div class="absolute bottom-0 left-0 px-6 py-4 w-full bg-primary">
            <div class="relative h-[52px] mb-2">

                <livewire:search-component direction="top" />
            </div>
            {{-- <div class="flex justify-between py-2 w-full text-white">
                @foreach (config('social-media.networks') as $network => $data)
                <a href="{{ $data['url'] }}" target="_blank" aria-label="Visitar {{ $network }}">
                    <x-dynamic-component :component="$data['icon']" class="w-8 h-8" />
                </a>
                @endforeach
            </div> --}}
        </div>
    </div>

</header>


<style>
    .circle-animate {
        display: none;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const hamburgerButton = document.querySelector('[x-ref="hamburgerButton"]');
        const menuAnimate = document.querySelector('.menu-animation');
        const exitAnimate = document.querySelector('[x-ref="exitButton"]');
    });
</script>
