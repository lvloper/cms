@php
$menu = App\Models\Menu::query()->where('slug', 'header')->first();
@endphp
@persist('header')
<header data-aos="slide-down" data-aos-delay="900" class="top-0 z-50 w-full bg-gray-200 md:fixed"
    x-data="{ 
        scrolled: false, 
        currentActive: null,
        currentSubmenu: null,
        calculateDropdownPosition(parentKey) {
            return {
                left: document.querySelector('.parent-item-' + parentKey).offsetLeft + 5 + 'px',
            }
        }
     }"
    wire:ignore
    @mouseleave="currentActive = null; $dispatch('closeheader')"
    @closeheader.window="currentActive = null;"
    x-init="window.addEventListener('scroll', () => { scrolled = window.scrollY > 16; })"
    :class="{ 'sticky-active': scrolled || currentActive }">
   
    <div class="flex justify-between px-6 md:px-[7rem] items-center h-[80px] md:h-[60px] bg-white">
        <div class="xl:pl-6">
            <a href="/" wire:navigate.hover>
                <img class="xl:w-[80%] md:-translate-x-4" src="{{ asset('img/layout/logo.svg') }}" alt="">
            </a>
        </div>
        <div>
            <div class="hidden gap-6 py-4 text-lg md:flex">
                @if ($menu)
                @foreach ($menu->items as $parentKey => $item)
                <div class="parent-item-{{ $parentKey }}" 
                     @mouseover="currentActive = '{{ $parentKey }}'">
                    <x-link class="font-medium transition-all duration-200 hover:text-primary" :attrs="$item['route']">{{
                        $item['label'] }}</x-link>
                </div>
                @endforeach
                @endif
            </div>
        </div>
    </div>
    <div class="dropdowns bg-gray-2">
        <div class="">
        @foreach ($menu->items as $parentKey => $item)
            @if( $item['children'] )
            <div class="relative font-bold transition-height"
            x-show="currentActive == '{{ $parentKey }}'"
            x-cloak
            :class="{ 'menu-open': currentActive == '{{ $parentKey }}', 'h-0': currentActive != '{{ $parentKey }}' }"
            :style="calculateDropdownPosition('{{ $parentKey }}')">
                <ul class="relative py-6">
                    @isset( $item['children'] )
                    @foreach ($item['children'] as $childKey => $child)
                        <li class="px-4 leading-loose" @click="currentActive = null; currentSubmenu = null" 
                         @mouseover="currentSubmenu = '{{ $childKey }}'">
                            <x-link 
                            class="hover:text-primary" :attrs="$child['route']">{{
                        $child['label'] }} </x-link>
                        </li>
                        @if( $child['children'] )
                        <ul class="absolute top-0 -left-[250px] py-6 w-[250px] font-medium text-right"
                            x-show="currentSubmenu == '{{ $childKey }}'" 
                            x-cloak>
                            @isset( $child['children'] )
                            @foreach ($child['children'] as $childKey => $subchild)
                            <li class="px-4 leading-snug">
                                <x-link @click="currentSubmenu = null; currentActive = null" class="hover:text-primary" :attrs="$subchild['route']">{{
                                $subchild['label'] }}</x-link>
                            </li>
                            @endforeach
                            @endisset
                        </ul>
                        @endif
                    @endforeach
                    @endisset
                </ul>
            </div>
            @endif
        @endforeach
        </div>
    </div>
    <div x-show="currentActive == null" class="flex justify-between px-4 md:px-[7rem] bg-gradient-secondary-primary bg-size-125 bg-position-center py-3 md:h-[60px] items-center">
        <div  class="flex justify-center items-center px-2 py-1 text-xs font-bold text-center bg-white rounded-sm">
            ACERQUEMOS VIH A MÁS PERSONAS
        </div>
        <a href="https://asociate.huesped.org.ar/vos2" target="_blank"
            class="text-xs font-bold text-center text-white transition-all duration-200 hover:border-b hover:border-white md:text-sm">
            HACE TU DONACIÓN
        </a>
    </div>
    <div x-show="currentActive" class="h-1 bg-gradient-secondary-primary bg-size-125 bg-position-center"></div>

</header>
@endpersist
{{-- <x-common.menu-mobile/> --}}