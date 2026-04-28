<x-layout>
    <div id="post-container" class="relative">
        <div class="relative bg-animate bg-white md:bg-primary fade-in">
            <div class="pt-16 md:pt-[4rem] text-black md:text-white pb-[8rem] @if ($blog->image) md:pb-[22rem] @endif">
                <div class="container-notice flex flex-col">

                    <div class="md:justify-between md:flex order-2 md:order-1 mb-6 md:mb-0">
                        <div class="flex gap-2 items-center">
                            <svg class="w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"
                                class="lucide lucide-clock-5">
                                <circle cx="12" cy="12" r="10" />
                                <polyline points="12 6 12 12 14.5 16" />
                            </svg>
                            <div>{{ $blog->published_at ? $blog->published_at->diffForHumans() : 'Fecha no disponible'
                                }}</div>
                        </div>
                        <div class="text-sm">{{ $blog->formattedPublishedDate() }}</div>
                    </div>

                    <div x-cloak x-data
                        class="overflow-hidden order-1 md:order-2 title-container text-xl md:text-5xl xl:text-5xl font-heavy md:leading-tight md:py-8">
                        <h1 class="title-animate mb-8 lg:mb-2  ">
                            {{ Str::of($blog->title)->trim() }}
                        </h1>
                    </div>



                    <div class="text-lg xl:text-xl md:leading-t fade-in2 description-container order-3">
                        {!! $blog->description !!}
                    </div>
                </div>
            </div>
        </div>


        <div class="relative">
            <div class="bg-trigger" x-cloak x-data>
                @if ($blog->image)
                <x-image :image="$blog->image" alt="{{ $blog->name }}"
                    imageClass="max-h-[80dvh] w-full h-full object-contain"
                    class="bigPicture container w-full h-img -mt-[4rem] sm:-mt-60 md:-mt-96 lg:-mt-72" />
                @endif
            </div>

            <div class="grid pt-16 pb-16 container-notice main-container text-wysiwyg  mx-auto">
                @php
                    $contentHtml = $blog->content ?? '';

                    if ($blog->id < 812) {
                        $pattern = '/<p[^>]*>\s*La entrada .*? se publicó primero en .*?<\/p>/si';
                        $contentHtml = preg_replace($pattern, '', $contentHtml);
                    }
                @endphp

                {!! $contentHtml !!}
                <br>
                <x-blog.tags :blog="$blog" />
            </div>



            <div class="flex justify-between mb-16 container-notice">
                @if($previous = $blog->previous())
                <a wire:navigate href="{{ $previous->url }}"
                    class="flex gap-2 text-sm text-primary font-heavy max-w-[40%] place-items-center group">
                    <x-lucide-move-left class="w-5 transition-transform group-hover:-translate-x-1" />
                    <span>{{ $previous->title }}</span>
                </a>
                @endif

                @if($next = $blog->next())
                <a wire:navigate href="{{ $next->url }}"
                    class="flex gap-2 text-sm text-right text-primary font-heavy max-w-[40%] place-items-center group">
                    <span>{{ $next->title }}</span>
                    <x-lucide-move-right class="w-5 transition-transform group-hover:translate-x-1" />
                </a>
                @endif
            </div>
        </div>



    </div>
    <x-common.floating-share :url="$blog->url" />
    <x-blog.related-posts :blog="$blog" />

    <div id="progress-bar" class="sticky bottom-0 left-0 z-50 w-[0] h-1 bg-primary"></div>


</x-layout>


@if ($blog->isOldBlog())
<style>
    .aligncenter {
        display: block;
        margin: 0 auto;
    }

    .alignnone {

        float: left;
        padding-right: 1rem;
    }

    .alignnone.size-medium {
        width: 50%;
        height: auto;
        max-width: 100%;
        margin-top: 1rem;
        margin-bottom: 1rem;
    }

    .alignnone.size-medium:nth-child(odd) {
        padding-left: 0.5rem;
        padding-right: 0;
    }

    .alignnone.size-medium:nth-child(even) {
        padding-right: 0.5rem;
        padding-left: 0;
    }

    .title-animate {
        transform: translateY(calc(100% + 100px));
    }

    .fade-in2 {
        opacity: 0;
    }
</style>

<script>
    document.addEventListener('livewire:navigated', function() {
        const BLOCK = document.getElementById('post-container');

        const headerImage = document.querySelector('.bigPicture img');
        const contentImages = document.querySelectorAll('.main-container img');

        if (headerImage) {
            const headerSrc = headerImage.src;

            contentImages.forEach(img => {
                if (img.src === headerSrc) {
                    img.parentElement.style.display = 'none';
                }
            });
        }



    });


</script>
@endif


<script>
    console.dir('si')
    document.addEventListener('livewire:navigated', function() {

        const BLOCK = document.getElementById('post-container');

        const title = BLOCK.querySelector('.title-animate');
        const titleContainer = BLOCK.querySelector('.title-container');
        const fadeIn2 = BLOCK.querySelector('.fade-in2');

        setTimeout(() => {
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

            gsap.to(fadeIn2, {
                opacity: 1,
                duration: 1,
                delay: .5,
                ease: "power3.out"
            });

        }, 300);


        const bgAnimate = document.querySelector('.bg-animate');
        const trigger = document.querySelector('.bg-trigger');
        const progressBar = document.getElementById('progress-bar');
        const postContainer = document.getElementById('post-container');

        gsap.to(bgAnimate, {
            backgroundColor: "#fff",
            scrollTrigger: {
                trigger: trigger,
                start: "top top",
                end: "bottom bottom",
                scrub: .5,
                toggleActions: 'play none none reverse',
            },
        });

        gsap.to(progressBar, {
            width: "100%",
            ease: "none",
            scrollTrigger: {
                trigger: postContainer,
                start: "top top",
                end: "bottom bottom",
                scrub: 0.3,
            },
        });
    });
</script>
