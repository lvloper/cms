<x-block>
    <div class="grid relative grid-cols-1 bg-black md:grid-cols-2">
        <div class="flex relative justify-center items-center py-8 md:justify-end md:pr-24 md:py-16 md:py-0">
            <x-image :image="$image" :imageMobile="$image ?? null" class="absolute inset-0"
                imageClass="background-size: cover; background-position: center; background-repeat: no-repeat"
                :background="true" />

            @if($title)
            <div class="text-xl font-bold text-center text-white uppercase drop-shadow-2xl md:text-3xl">
                <span class="drop-shadow-custom">{{ $title }}</span>
            </div>
            @endif
        </div>

        <div
            class="flex relative z-10 justify-center items-center px-4 py-8 text-center bg-white md:pl-12 md:justify-start bg-gray-3 md:bg-white md:py-0">
            <div class="flex flex-row gap-0 justify-between items-center w-full">
                @foreach($numbers as $item)
                <div class="flex flex-wrap justify-center px-2 md:px-10 py-2 md:py-4 {{ !$loop->last ? 'border-r border-primary' : 'border-r-0' }}"
                    x-data="{ 
                            currentNumber: 0,
                            targetNumber: {{ $item['number'] }},
                            formatNumber(num) {
                                return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.')
                            }
                         }" x-init="() => {
                            ScrollTrigger.create({
                                trigger: $el,
                                onEnter: () => {
                                    gsap.to($data, {
                                        currentNumber: targetNumber,
                                        duration: 1,
                                        ease: 'power1.out',
                                        onUpdate: () => {
                                            $el.querySelector('.number').textContent = formatNumber(Math.round(currentNumber))
                                        }
                                    })
                                }
                            })
                         }">
                    <div class="mb-1 text-xl font-bold md:text-2xl text-primary md:mb-2 number">0</div>
                    <h3 class=" leading-tight h-[40px] text-md md:text-md mb-1 md:mb-2 max-w-max">{{ $item['label'] }}
                    </h3>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</x-block>