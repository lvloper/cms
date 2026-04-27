<x-block>
    @php
    $id = 'hero-' . uniqid();
    $estilos = $estilos ?? '1';
    @endphp
    <div id="{{ $id }}" class="{{ $estilos == '1' ? 'pt-6 md:pt-20' : '' }} style-{{ $estilos }} bg-gray-3">
        <div class="{{ $estilos == '1' ? '' : 'bg-primary' }}">
            <div class="relative" style="z-index: 1;">
                <div class="flex items-start justify-start container">
                    <h1 class="title-animate uppercase {{ $estilos == '1' ? 'text-primary ' : 
                        'text-white pt-16 md:pt-20 lg:pt-18 max-w-[800px]' }} 
                        font-bold lg:leading-[1] lg:mb-[-7px]
                          
                        {{ strlen($title) > 8 ? 'text-xl lg:text-5xl mb-0  leading-snug  mx-0' 
                                            : 'text-3xl lg:text-5xl leading-snug ' }}">
                        {!! nl2br($title ?? '') !!}
                    </h1>
                </div>
            </div>
        </div>
        <div style="z-index: 1;" class="relative pt-[2rem] md:pt-[4rem] 2xl:pt-[4rem] font-sans leading-normal 
            {{ $estilos == '1' ? 'text-white bg-primary' : ' bg-white' }}">
            <div class="container">
                @isset($description)
                <div class="md:w-[90%] xl:w-[70%] 2xl:w-[65%] description2-normal mb-2 futura-light 
                description-animate">
                    {!! $description !!}
                </div>
                @endif
                @isset($description2)
                <div
                    class="normal-text w-[90%] md:w-[90%] xl:w-[70%] 2xl:w-[65%] description-normal description-animate">
                    {!! $description2 !!}
                </div>
                @endif
                <div class="flex justify-end mt-2 pb-2 md:pb-[2rem]">
                    <svg xmlns="http://www.w3.org/2000/svg" width="60" height="60" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round"
                        class="lucide lucide-arrow-down pulse-down">
                        <path d="M12 5v14" />
                        <path d="m19 12-7 7-7-7" />
                    </svg>
                </div>
            </div>
        </div>

    </div>


    @pushOnce('scripts', 'block-hero')
    <style>
        .title-animate {
            transform: translateY(150px);
        }

        .style-2 .title-animate {
            transform: translateY(180px);
        }

        .description-animate {
            opacity: 0;
            transform: translateY(30px);
        }
    </style>
    @endpushOnce

    <script>
        document.addEventListener('livewire:navigated', function() {
            const BLOCK = document.getElementById('{{ $id }}');
            if (!BLOCK) return;
            const title = BLOCK.querySelector('.title-animate');
            const descriptions = BLOCK.querySelectorAll('.description-animate');
            const arrowDown = BLOCK.querySelector('.lucide-arrow-down');

            if (title && typeof gsap !== 'undefined') {
            gsap.to(title, {
                scrollTrigger: {
                    trigger: BLOCK,
                    start: "top 80%",
                    end: "bottom 20%",
                    toggleActions: 'play reset play reset'
                },
                y: 0,
                duration: 1,
                ease: "power3.out"
            });
            }

            descriptions.forEach((desc, index) => {
                gsap.to(desc, {
                    scrollTrigger: {
                        trigger: desc,
                        start: "top 80%",
                        toggleActions: 'play reset play reset',
                    },
                    y: 0,
                    opacity: 1,
                    duration: 1,
                    delay: .5,
                    ease: "power2.out"
                });
            });

            if (arrowDown) {
                arrowDown.style.cursor = 'pointer';
                arrowDown.addEventListener('click', () => {
                    const blockBottom = BLOCK.offsetTop + BLOCK.offsetHeight;
                    gsap.to(window, {
                        scrollTo: blockBottom - 50,
                        duration: 1,
                        ease: "power2.inOut"
                    });
                });
            }
    });
    </script>
</x-block>