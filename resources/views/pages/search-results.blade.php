<x-layout >
    <div class="pt-0 bg-gray-3">
        <div class="bg-primary">
            <div class="relative" style="z-index: 1;">
                <div class="flex items-start justify-start container">
                    <h1 class="title-animate uppercase text-white pt-16 md:pt-20 lg:pt-18 max-w-[800px] font-bold lg:leading-[1] lg:mb-[-16px] text-xl lg:text-5xl leading-snug">
                        Resultados de búsqueda
                    </h1>
                </div>
            </div>
        </div>
        <div style="z-index: 1;" class="relative md:pt-[4rem] 2xl:pt-[4rem] font-sans leading-normal bg-white">
            <div class="container">

            
                <div class="py-2 lg:py-10 bg-white">
                    <livewire:search-component :isFullPage="true" />
                </div>

                {{-- <div class="md:w-[90%] xl:w-[70%] 2xl:w-[65%] description2-normal mb-2 futura-light description-animate">
                    @if($searchTerm)
                        <p>Resultados para: <strong>"{{ $searchTerm }}"</strong></p>
                    @else
                        <p>Utiliza el campo de búsqueda para encontrar contenido en nuestro sitio.</p>
                    @endif
                </div> --}}
            </div>
        </div>
    </div>


    @pushOnce('scripts', 'block-hero')
    <style>
        .title-animate {
            transform: translateY(150px);
        }
        .description-animate {
            opacity: 0;
            transform: translateY(30px);
        }
    </style>
    @endpushOnce

    <script>
        document.addEventListener('livewire:navigated', function() {
            const title = document.querySelector('.title-animate');
            const descriptions = document.querySelectorAll('.description-animate');

            gsap.to(title, {
                scrollTrigger: {
                    trigger: title,
                    start: "top 80%",
                    end: "bottom 20%",
                    toggleActions: 'play reset play reset'
                },
                y: 0,
                duration: 1,
                ease: "power3.out"
            });

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
        });
    </script>
</x-layout>
