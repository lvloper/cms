@php
    use Illuminate\Support\Str;

    $images = $images ?? [];
    $type = $type ?? 'grid_lightbox';
    $id = 'gallery-' . Str::random(8);
@endphp

<div class="my-8 gallery-block w-full max-w-screen-lg mx-auto">
    @if(count($images) > 0)

        {{-- 1. Grilla 3 columnas + Lightbox --}}
        @if($type === 'grid_lightbox')
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 w-full max-w-screen-lg mx-auto" id="{{ $id }}">
                @foreach($images as $image)
                    <div class="relative overflow-hidden group cursor-pointer aspect-[4/3]"
                         onclick="openLightbox('{{ Storage::url($image) }}')"> <!-- Placeholder for lightbox trigger -->

                         {{-- Using a simplified fancybox approach or similar if library available,
                              for now using a simple link wrapper assuming a lightbox script handles it
                              based on class or attributes. Since no specific lightbox lib was found in views,
                              we'll implement a basic structure that can be hooked into. --}}
                        <a href="{{ Storage::url($image) }}"
                           data-fslightbox="gallery-{{ $id }}"
                           class="block w-full h-full">
                            <img src="{{ Storage::url($image) }}"
                                 alt=""
                                 loading="lazy"
                                 class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
                        </a>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- 2. Carousel con foto principal --}}
        @if($type === 'carousel_main')
             <div class="space-y-4 w-full max-w-screen-lg mx-auto" x-data="{ activeImage: '{{ Storage::url($images[0]) }}' }">
                {{-- Main Image --}}
                <div class="w-full max-w-screen-lg aspect-[4/3] overflow-hidden rounded-lg mx-auto">
                    <img :src="activeImage" class="w-full h-full object-cover transition-opacity duration-300" alt="">
                </div>

                {{-- Thumbs --}}
                <div class="relative w-full max-w-screen-lg mx-auto">
                    <swiper-container
                        class="swiper-carrousel-news w-full"
                        space-between="10"
                        slides-per-view="2.2"
                        navigation-next-el=".{{ $id }}-thumbs-next"
                        navigation-prev-el=".{{ $id }}-thumbs-prev"
                        breakpoints='{"640": {"slidesPerView": 3}, "768": {"slidesPerView": 4}, "1024": {"slidesPerView": 5}, "1440": {"slidesPerView": 6}}'
                    >
                        @foreach($images as $image)
                            <swiper-slide class="cursor-pointer opacity-70 hover:opacity-100 transition-opacity"
                                 @click="activeImage = '{{ Storage::url($image) }}'">
                                <div class="aspect-[4/3] rounded overflow-hidden border-2 border-transparent hover:border-primary">
                                    <img src="{{ Storage::url($image) }}" class="w-full h-full object-cover" alt="">
                                </div>
                            </swiper-slide>
                        @endforeach
                    </swiper-container>

                    <div class="{{ $id }}-thumbs-prev absolute -left-6 lg:-left-10 top-1/2 -translate-y-1/2 swiper-custom-buttons">
                        <x-lucide-chevron-left class="w-8 h-8 lg:w-12 lg:h-12 stroke-2 text-primary" />
                    </div>
                    <div class="{{ $id }}-thumbs-next absolute -right-6 lg:-right-10 top-1/2 -translate-y-1/2 swiper-custom-buttons">
                        <x-lucide-chevron-right class="w-8 h-8 lg:w-12 lg:h-12 stroke-2 text-primary" />
                    </div>
                </div>
            </div>
        @endif

        {{-- 3. Carousel con Lightbox --}}
        @if($type === 'carousel_lightbox')
            <div class="relative w-full max-w-screen-lg mx-auto">
                <swiper-container
                    class="swiper-carrousel-news w-full pb-10"
                    navigation="true"
                    pagination="false"
                    space-between="20"
                    slides-per-view="1.1"
                    navigation-next-el=".{{ $id }}-swiper-button-next"
                    navigation-prev-el=".{{ $id }}-swiper-button-prev"
                    breakpoints='{"640": {"slidesPerView": 1.5}, "1024": {"slidesPerView": 2.5}, "1440": {"slidesPerView": 3.2}}'
                    style="--swiper-navigation-color: theme('colors.primary'); --swiper-pagination-color: theme('colors.primary');"
                >
                    @foreach($images as $image)
                        <swiper-slide>
                            <a href="{{ Storage::url($image) }}"
                               data-fslightbox="gallery-{{ $id }}"
                               class="block aspect-[4/3] overflow-hidden rounded-lg">
                                <img src="{{ Storage::url($image) }}"
                                     alt=""
                                     loading="lazy"
                                     class="w-full h-full object-cover hover:scale-105 transition-transform duration-500">
                            </a>
                        </swiper-slide>
                    @endforeach
                </swiper-container>

                <div class="{{ $id }}-swiper-button-prev absolute -left-6 lg:-left-10 top-1/2 -translate-y-1/2 swiper-custom-buttons">
                    <x-lucide-chevron-left class="w-8 h-8 lg:w-12 lg:h-12 stroke-2 text-primary" />
                </div>
                <div class="{{ $id }}-swiper-button-next absolute -right-6 lg:-right-10 top-1/2 -translate-y-1/2 swiper-custom-buttons">
                    <x-lucide-chevron-right class="w-8 h-8 lg:w-12 lg:h-12 stroke-2 text-primary" />
                </div>
            </div>
        @endif

    @endif
</div>

{{--
    Note: For Lightbox functionality, we've added 'data-fslightbox' attributes.
    You will need to install and include fslightbox or a similar library
    in your app.js or layout if it's not already present.
    Since I didn't find a lightbox library in the file search,
    I'll ensure the Script tag is added if you don't have one globally.
--}}
@once
    @push('scripts')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/fslightbox/3.0.9/index.js"></script>
    @endpush
@endonce

@once
    @push('styles')
        <style>
            /* Basic fallback styles so slides don't stack oddly if Swiper assets aren't present */
            .gallery-block swiper-container {
                display: block;
                width: 100%;
            }
            .gallery-block swiper-slide {
                width: 100%;
                height: auto;
            }
            /* Hide default Swiper bullets for gallery carousels */
            .gallery-block .swiper-pagination-bullet,
            .gallery-block .swiper-pagination {
                display: none !important;
            }
        </style>
    @endpush
@endonce
