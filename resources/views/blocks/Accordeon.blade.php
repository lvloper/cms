<x-block>
    @if (count($items) > 0)
    @php
    $firstItem = reset($items);
    @endphp

    <x-common.line-title title="{{ $title }}" />

    <div x-data="{
            activeAccordion: window.innerWidth >= 1024 ? 0 : null,
            item: {
                title: '{{ $firstItem['title'] }}',
                description: '{{ addslashes($firstItem['description']) }}',
                images: {{ json_encode($firstItem['images']) }},
            },
            accordionId: window.innerWidth >= 1024 ? 0 : null,
            init() {
                // Actualizar en redimensionamiento de pantalla
                this.$watch($screen('lg'), matches => {
                    if (matches) {
                        this.activeAccordion = 0;
                        this.accordionId = 0;
                    } else {
                        this.activeAccordion = null;
                        this.accordionId = null;
                    }
                });
            }
        }" class="relative w-full py-4 md:py-24 lg:pt-[4rem] lg:pb-[6rem] mx-auto ">

        <div class="container lg:grid lg:grid-cols-2 lg:gap-x-24 2lg:gap-x-0" x-ref="container">



            <div class="lg:col-span-1 2lg:w-[75%]">

                <div class="gap-4 ">
                    <div class="pb-4 normal-text leading-tight lg:pb-2 ">
                        {!! $description ?? '' !!}
                    </div>
                    <div class="pb-4 leading-normal normal-text font-poppins hasStrongRed-sm">
                        {!! $description2 ?? '' !!}
                    </div>
                </div>
                @foreach ($items as $index => $item)
                <div class="-mx-4 duration-200 ease-out cursor-pointer md:-mx-0 group  item-animate">
                    <div>
                        <button @click="
                                    activeAccordion = activeAccordion === {{ $index }} ? null : {{ $index }};
                                    accordionId = {{ $index }}; $nextTick(()=> { })"
                                     class="flex gap-2 md:mb-4 items-center px-1 py-2 max-w-[450px] w-full justify-between bg-gray-50 font-semibold text-left border-t-4 select-none md:px-8 group md:border-b-4 md:border-t-0 border-secondary" :class="activeAccordion === {{ $index }} ? 'text-primary' : ''">
                            <span
                                class="w-full leading-none text-base font-bold leading-tight group-hover:text-primary transition-all duration-400 sm:w-[80%]"
                                :class="activeAccordion === {{ $index }} ? 'text-primary' : ''">{{
                                $item['title'] ?? '' }}</span>
                            <div class="flex justify-end">
                                <template x-if="activeAccordion === {{ $index }}">
                                    <svg class="w-6 md:w-8 text-primary border-2 border-primary rounded-full duration-300 ease-out rotate-45"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.25" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 18V6m6 6H6" />
                                    </svg>
                                </template>
                                <template x-if="activeAccordion !== {{ $index }}">
                                    <svg class="w-6 md:w-8 text-primary border-2 border-primary rounded-full duration-300 ease-out group-hover:-rotate-[45deg]"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.25" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6" />
                                    </svg>
                                </template>
                            </div>
                        </button>
                    </div>
                    {{-- acc mobile --}}
                    <div class="lg:hidden" x-show="activeAccordion === {{ $index }}" x-collapse x-cloak>
                        <div class="bg-white border-t-4 border-secondary">
                            <div class="pb-4 pt-12 !px-4 lg:pb-8 text-lg text-primary leading-tight font-bold">
                                {{ $item['title'] ?? '' }}
                            </div>
                            <div class="px-4 pb-4  leading-snug text-normal font-poppins tick-in-li">
                                {!! $item['description'] ?? '' !!}
                            </div>

                            <x-link :attrs="$item['route']" :hideIfNull="true">
                                <button class="px-6 py-2 mx-8 mb-8 text-white rounded-full text-md bg-secondary">
                                    Más info
                                </button>
                            </x-link>

                            @if (is_array($item['images']) && count($item['images']) > 0 || $item['images'] )
                            <div>
                                <swiper-container class="mySwiper max-h-[20rem] " navigation="true">
                                    @if (is_array($item['images']))
                                    @foreach ($item['images'] as $image)
                                    <swiper-slide>
                                        <img src="/storage/{{ $image }}" class="accordeon-img">
                                    </swiper-slide>
                                    @endforeach
                                    @else
                                    <swiper-slide>
                                        <img src="/storage/{{ $item['images'] }}" class="accordeon-img">
                                    </swiper-slide>
                                    @endif
                                </swiper-container>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            {{-- acc desktop --}}
            <div class="hidden lg:block" style="height: calc(100% + 120px);">
                @foreach ($items as $index => $item)
                <div :class="activeAccordion === {{ $index }} ? 'lg:block' : 'hidden'" class="mx-auto sticky top-32">
                    <div class="pb-8 mx-auto bg-white border-t-4 border-secondary" x-show="activeAccordion !== null"
                        x-collapse x-cloak>
                        <div class="pb-4 pt-6 !px-8 text-lg text-primary leading-tight font-bold">
                            {{ $item['title'] ?? '' }}
                        </div>
                        <div class="px-6 pb-4  leading-snug text-normal font-poppins tick-in-li">
                            {!! $item['description'] ?? '' !!}
                        </div>

                        <x-link :attrs="$item['route']" :hideIfNull="true">
                            <button class="px-6 py-2 mx-8 text-white rounded-full text-md bg-secondary">
                                Más info
                            </button>
                        </x-link>

                        @if (is_array($item['images']) && count($item['images']) > 0 || $item['images'] )
                        <div>
                            <swiper-container class="mySwiper max-h-[24rem] mt-4 -mb-8" navigation="true">
                                @if (is_array($item['images']))
                                @foreach ($item['images'] as $image)
                                <swiper-slide>
                                    <img src="/storage/{{ $image }}" class="accordeon-img">
                                </swiper-slide>
                                @endforeach
                                @else
                                <swiper-slide>
                                    <img src="/storage/{{ $item['images'] }}" class="accordeon-img">
                                </swiper-slide>
                                @endif
                            </swiper-container>
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>

        </div>
    </div>

    <style>
        .accordeon-img {
            width: 100% !important;
        }

        .item-animate {
            opacity: 0;
        }

        #info-container {
            opacity: 0;
        }
    </style>

    @pushOnce('scripts')
    <script>
        document.addEventListener('livewire:navigated', function() {
                    const BLOCK = document.getElementById('{{ $id }}');
                    const items = document.querySelectorAll('.item-animate');
                    const container = document.getElementById('info-container');

                    items.forEach((item, index) => {
                        gsap.to(item, {
                            scrollTrigger: {
                                trigger: BLOCK,
                                start: "top 50%",
                                end: "bottom top",
                                toggleActions: 'play'
                            },
                            y: 0,
                            opacity: 1,
                            duration: 0.5,
                            delay: index * 0.1,
                            ease: "power2.out"
                        });
                    });

                    gsap.to(container, {
                        scrollTrigger: {
                            trigger: BLOCK,
                            start: "top 80%",
                            end: "bottom 20%",
                            toggleActions: 'play reset play reset'
                        },
                        y: 0,
                        opacity: 1,
                        duration: 1,
                        ease: "power2.out"
                    });
                });
    </script>
    @endpushOnce

    @endif
</x-block>