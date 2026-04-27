@props(['style' => 'full', 'images', 'id' => 'gallery-'.uniqid(), 'auto_play' => false])
<x-block class="{{ isset($preview) ? 'min-h-[300px]' : '' }} relative">
    <div class="relative {{ $style == 'container' ? 'gallery-container' : $style }}">
        <div class="gallery-carousel">
            <swiper-container class="swiper-container swiper-carrousel-gallery swiper-carrousel-wide-pagination"
                slides-per-view="1" pagination="true" pagination-clickable="true" space-between="0"
                navigation-next-el=".{{ $id }}-swiper-button-next" navigation-prev-el=".{{ $id }}-swiper-button-prev"
                pagination-el=".{{ $id }}-bullets" init="false" @if($auto_play) autoplay-delay="5000" @endif>
                @foreach ($images as $item)
                <swiper-slide>
                    @if (!is_string($item) && isset($item['route']))
                    <x-link :attrs="$item['route']" class="w-full" disableWireNavitage="false">
                        <x-image imageClass="object-contain max-h-[980px] mx-auto" :image="$item['image']" class="object-contain w-full h-full  max-h-[980px]" />
                    </x-link>
                    @else
                    <x-image imageClass="object-contain max-h-[980px] mx-auto" :image="$item" class="object-contain w-full h-full max-h-[980px]"  />
                    @endif
                </swiper-slide>
                @endforeach
            </swiper-container>

            <div class="navigation-button navigation-button-prev swiper-custom-buttons {{ $id }}-swiper-button-prev">
                <x-lucide-chevron-left class="icon" />
            </div>
            <div class="navigation-button navigation-button-next swiper-custom-buttons {{ $id }}-swiper-button-next">
                <x-lucide-chevron-right class="icon" />
            </div>
        </div>
    </div>
    <div class="{{ $id }}-bullets pagination swiper-cool-bullets"></div>
</x-block>