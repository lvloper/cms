@php
$menu = App\Models\Menu::query()->where('slug', 'header')->first();
@endphp
<header id="mainHeader" data-aos="slide-down" data-aos-delay="900" class="
hidden lg:block
top-0 z-50 w-full bg-white md:sticky" x-data="{ 
        resetValues: function() {
            this.menuBarMenu = null;
        },
        scrolled: false, 
        menuBarMenu: null,
        closeTimeout: null,
        closeMenu() {
            if (this.closeTimeout) clearTimeout(this.closeTimeout);
            this.closeTimeout = setTimeout(() => {
                this.menuBarMenu = null;
            }, 300);
        },
        cancelClose() {
            if (this.closeTimeout) clearTimeout(this.closeTimeout);
        }
     }" wire:ignore @mouseleave="closeMenu(); $dispatch('closeheader');" @closeheader.window="menuBarMenu = null;"
    x-init="window.addEventListener('scroll', () => { scrolled = window.scrollY > 16; })"
    :class="{ 'sticky-active': scrolled || menuBarMenu }">


    {{-- @persist('header') --}}
    <div class="flex justify-between px-6 container mx-auto items-center min-h-[80px] bg-white transition-all duration-300"
        :class="{ 'min-h-[46px]': scrolled }">
        <div class="py-2">
            <a href="/" wire:navigate.hover
                class="block text-xl font-bold uppercase tracking-wide text-primary transition-all duration-300"
                :class="{ 'text-base': scrolled }" :style="{ transition: 'all 0.3s ease-in-out' }">
                {{ config_text('site-name', 'CMS Base') }}
            </a>
        </div>

        <div class="flex-1 flex justify-end">
            <div class="hidden gap-6 py-4 text-lg md:flex">
                <div x-data="{
                    menuBarOpen: false, 
                    menuBarMenu: '',
                    closeTimeout: null,
                    closeMenu() {
                        if (this.closeTimeout) clearTimeout(this.closeTimeout);
                        this.closeTimeout = setTimeout(() => {
                            this.menuBarOpen = false;
                            this.menuBarMenu = '';
                        }, 300);
                    },
                    cancelClose() {
                        if (this.closeTimeout) clearTimeout(this.closeTimeout);
                    }
                }" @click.away="closeMenu()" @mouseleave="closeMenu()"
                    class="relative top-0 left-0 z-50 w-auto transition-all duration-150 ease-out">
                    <div class="relative top-0 left-0 z-40 w-auto h-10 transition duration-200 ease-out">
                        <div class="w-full h-full">
                            <div class="flex gap-6 w-full h-full select-none text-neutral-900">
                                @if ($menu)
                                @foreach ($menu->items as $parentKey => $item)
                                @php
                                $route = \App\Models\Route::find($item['route']['route_id']);
                                @endphp
                                <!-- Menu Button -->
                                <div class="relative h-full cursor-default" @mouseleave="closeMenu()">
                                    @if(isset($item['children']) && !empty($item['children']))
                                    <div class="h-full"
                                        @mouseover="cancelClose(); menuBarOpen = true; menuBarMenu = 'menu{{$parentKey}}'">
                                        <x-link allowWireNavitage
                                            class="flex items-center h-full gap-2 font-medium transition-all duration-200 hover:text-primary text-base {{ $route && !$item['route']['anchor'] && $route->isActiveParent() ? 'text-primary' : '' }}"
                                            :attrs="$item['route']">
                                            {{ $item['label'] }}

                                            <i class="fa-solid fa-chevron-down text-[10px]"></i>
                                        </x-link>
                                    </div>

                                    <div x-show="menuBarMenu == 'menu{{$parentKey}}'" x-cloak @mouseover="cancelClose()"
                                        x-transition:enter="transition ease-linear duration-100"
                                        x-transition:enter-start="-translate-y-1 opacity-90"
                                        x-transition:enter-end="translate-y-0 opacity-100"
                                        x-transition:leave="transition ease-linear duration-100"
                                        x-transition:leave-start="translate-y-0 opacity-100"
                                        x-transition:leave-end="-translate-y-1 opacity-90"
                                        class="absolute left-0 z-50 p-4 mt-2 space-y-4 bg-white border rounded-lg shadow-lg w-max">
                                        @foreach ($item['children'] as $child)
                                        @php
                                        $childRoute = \App\Models\Route::find($child['route']['route_id']);
                                        $hasAnchor = $child['route']['anchor'] ?? false;
                                        @endphp
                                        <x-link allowWireNavitage
                                            class="block transition-all duration-200 hover:text-primary text-base {{ $childRoute && $childRoute->isActive() && !$hasAnchor ? 'text-primary font-medium' : '' }}"
                                            :attrs="$child['route']">
                                            {{ $child['label'] }}
                                        </x-link>
                                        @endforeach
                                    </div>
                                    @else
                                    <x-link allowWireNavitage
                                        class="flex items-center h-full transition-all duration-200 hover:text-primary text-base {{ $route && $route->isActive() ? 'text-primary font-medium' : '' }}"
                                        :attrs="$item['route']">
                                        {{ $item['label'] }}
                                    </x-link>
                                    @endif
                                </div>
                                @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="w-[150px]"></div>
    </div>
    {{-- @endpersist --}}

    <div x-show="menuBarMenu" class="h-1 bg-gradient-secondary-primary bg-size-125 bg-position-center"></div>
</header>
<div class="spader-header"></div>
<x-common.menu-mobile />
