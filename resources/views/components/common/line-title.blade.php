@php
$animate = $animate ?? true;
$id = uniqid('line-title-');

$titleLength = strlen(strip_tags($title));

$color = $color ?? 'black';
$sizeClass = $size ?? 'xl';
$longText = $titleLength > 8;
@endphp

<div wire:ignore>
    <div id="{{ $id }}" class="line-title-{{ $sizeClass }} base-line-title">
        <div class="pt-2 {{ $class ?? '' }} {{ $longText ? 'line-title-long' : '' }} overflow-hidden">
            <div class="{{ $containerClass ?? 'container' }}">
                <h2
                    class="font-bold leading-none uppercase title futura-medium text-{{ $color }} lg:max-w-none title-animate pb-2">
                    {!! nl2br($title ?? '' ) !!}
                </h2>
            </div>
            <div class=" line bg-primary h-[2px]"></div>
        </div>

        @if($animate)
        
        @pushOnce('scripts', 'block-title')
        <style>
            .title-animate {
                transform: translateY(150px);
            }
        </style>
        @endpushOnce
        <script>
            document.addEventListener('livewire:navigated', function() {
            const BLOCK = document.getElementById('{{ $id }}');
            if( BLOCK ) {
                const title = BLOCK.querySelector('.title-animate');
                if (title && typeof gsap !== 'undefined') {
                gsap.to(title, {
                    scrollTrigger: {
                        trigger: BLOCK,
                        start: "top bottom",
                        end: "bottom 20%", 
                        toggleActions: 'play none none none',
                        once: true
                    },
                    y: 0,
                    duration: 1,
                    delay: 0.1,
                    ease: "power3.out"
                    });
                }
            }

        });
        </script>
        
        @endif
    </div>

</div>