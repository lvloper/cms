@php
$id = 'features2-' . uniqid();
@endphp

<x-block>
    <div class="py-12 bg-white">
        <div class="container">
           {{-- @if($title)
                <x-common.line-title :title="$title" size="xl" class="mb-8" color="primary" />
            @endif --}}

            @if ($items)
            <div class="relative">
                <swiper-container class="swiper-features2"
                    slides-per-view="1.2"
                    space-between="16"
                    navigation-next-el=".{{ $id }}-swiper-button-next"
                    navigation-prev-el=".{{ $id }}-swiper-button-prev"
                    breakpoints='{
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
                    @foreach ($items as $item)
                    <swiper-slide class="h-auto">
                        <x-banners.box :item="$item" />
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

            </div>
            @endif
        </div>
    </div>

    @pushOnce('scripts','features2')
    <style>
        .swiper-features2 {
            padding: 20px 10px;
            margin: -20px -10px;
        }

        .swiper-features2::part(wrapper) {
            align-items: flex-start;
        }

        .swiper-features2::part(slide) {
            height: auto;
            align-self: flex-start;
        }

        .strong {
                strong {
                    font-weight: 700;
                }
            }

        .conteiner-features2 {
            opacity: 0;
            transform: translateY(30px);
        }
    </style>
    <script>
        document.addEventListener('livewire:navigated', function() {
            const BLOCK = document.getElementById('{{ $id }}');
            const container = document.querySelector('.conteiner-features2');
            if (!container || typeof gsap === 'undefined') return;

            gsap.to(container, {
                scrollTrigger: {
                    trigger: container,
                    start: "top 80%",
                    end: "bottom 0%",
                    toggleActions: 'play reset play reset'
                },
                y: 0,
                opacity: 1,
                duration: 1,
                offset: -150,
                delay: 0,
                ease: "power2.out"
            });
        });
    </script>
        @endpushOnce
</x-block>
