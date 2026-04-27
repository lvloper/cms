<x-block>

    <div class="py-4 md:py-12">
        <div class="container grid grid-cols-2 md:gap-4 lg:gap-12 2xl:gap-14">
            <div class="col-span-2 mx-auto lg:mx-0 lg:col-span-1 {{$position === 'right' ? 'lg:order-2' : ''}}">
                <div class="sticky about-image-container position-{{ $position }} max-w-[380px] float-right">
                    <div class="square-animate about-square">
                    </div>
                    <div class="about-image">
                        <x-image :image="$image" imageClass="object-contain" />

                    </div>
                </div>
            </div>
            <div class=" gap-4 pt-8 xl:w-[85%] col-span-2 lg:col-span-1 {{$position === 'right' ? 'lg:order-1' : ''}}">
                <div class="flex mb-6">
                    <div class="font-bold xl:col-span-4 text-primary font-poppins">
                        <p class="mb-2 leading-snug text-xl font-futura-bold">{{ $title ?? '' }}</p>
                        <p class="-mt-2 text-xl">{{ $title2 ?? '' }}</p>
                    </div>
                    {{-- <div class="hidden relative w-auto h-full lg:block">
                        <img class="hidden place-content-center mx-auto w-full xl:block"
                            src="{{ asset('img/zig-zag.svg') }}" alt="">
                    </div> --}}
                </div>
                <div class="grid gap-4 font-poppins text-base">
                    {!! $text ?? '' !!}
                </div>

                @if( isset($moreText) && $moreText )
                <div x-data="{ showMore: false, readMoreBtn: null }" x-init="readMoreBtn = $refs.readMoreBtn">
                    <span class="cursor-pointer font-poppins text-base text-primary text-underline" x-show="!showMore"
                        @click="showMore = !showMore" x-ref="readMoreBtn">Leer más</span>

                    <div class="grid gap-4 font-poppins text-base" x-show="showMore" x-cloak>
                        {!! $moreText ?? '' !!}
                    </div>
                    <span class="cursor-pointer font-poppins text-base text-primary text-underline" x-show="showMore"
                        @click="showMore = !showMore; $nextTick(() => readMoreBtn.scrollIntoView({ block: 'center' }))">Leer
                        menos</span>
                </div>
                @endif

            </div>
        </div>
    </div>


    {{-- @pushOnce('scripts', 'blocks-about')
    <script>
        document.addEventListener('livewire:navigated', function() {
                const BLOCK = document.getElementById('{{ $id }}');
                const img = BLOCK.querySelector('.about-image');
                const square = BLOCK.querySelector('.about-square');

                gsap.to([img, square], {
                    scrollTrigger: {
                        trigger: BLOCK,
                        start: "top 50%",
                        end: "bottom 20%",
                        toggleActions: 'play reset play reset'
                    },
                    x: 0,
                    y: 0,
                    duration: 1,
                    ease: "power3.out"
                });

            });
    </script>
    @endPushOnce --}}


</x-block>
