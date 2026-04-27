@php
$images = $images ?? [];
$totalImages = count($images);
$imagesPerStrip = ceil($totalImages / 2); // Desktop: 2 strips

// Mobile: Calculate how many strips we need (max 3)
$mobileStrips = min(3, $totalImages); // Don't create more strips than images
$imagesPerStripMobile = $totalImages > 0 ? ceil($totalImages / $mobileStrips) : 0;
@endphp

<x-block class="marquee-block">
    <div class="pb-4">
        @if ($title)
        <x-common.line-title title="{{ $title }}" />
        @endif

        @if ($description && strlen($description) >= 30)
        <div class="container">
            <div class="normal-text py-4 font-poppins xl:w-[80%] 2xl:w-[70%]">
                {!! $description ?? '' !!}
            </div>
        </div>
        @endif

        <div x-data="{
            vertical: false,
            setupMarquee() {
                // Clone groups for seamless scrolling
                const marquees = this.$el.querySelectorAll('.marquee');
                marquees.forEach(marquee => {
                    const group = marquee.querySelector('.marquee__group');
                    if (group) {
                        const clone = group.cloneNode(true);
                        clone.setAttribute('aria-hidden', 'true');
                        marquee.appendChild(clone);
                    }
                });
            }
        }" x-init="setupMarquee()" class="marquee-wrapper relative w-full">

            <!-- Desktop Version (2 strips) -->
            <div class="hidden md:flex flex-col gap-4">
                <!-- First strip -->
                <div class="marquee overflow-hidden">
                    <div class="marquee__group flex items-center justify-around gap-6 min-w-full">
                        @foreach ($images as $index => $item)
                        @if ($index < $imagesPerStrip) <div class="flex-shrink-0 marquee-item">
                            <x-image class=" w-auto object-contain" :image="$item" />
                    </div>
                    @endif
                    @endforeach
                </div>
            </div>

            <!-- Second strip (reverse) -->
            <div class="marquee marquee--reverse overflow-hidden">
                <div class="marquee__group flex items-center justify-around gap-6 min-w-full">
                    @foreach ($images as $index => $item)
                    @if ($index >= $imagesPerStrip)
                    <div class="flex-shrink-0 marquee-item">
                        <x-image class=" w-auto object-contain" :image="$item" />
                    </div>
                    @endif
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Mobile Version (dynamic strips based on image count) -->
        <div class="flex md:hidden flex-col gap-3">
            @for ($strip = 0; $strip < $mobileStrips; $strip++) <div
                class="marquee {{ $strip % 2 === 1 ? 'marquee--reverse' : '' }} overflow-hidden">
                <div class="marquee__group flex items-center justify-start gap-4 min-w-full">
                    @php
                    $stripImages = [];
                    // Distribute images evenly across strips
                    for ($i = $strip; $i < $totalImages; $i += $mobileStrips) {
                        if (isset($images[$i])) {
                            $stripImages[] = $images[$i];
                        }
                    }
                    @endphp
                        @foreach ($stripImages as $item) <div class="flex-shrink-0 marquee-item">
                        <x-image class=" w-auto object-contain" :image="$item" />
                </div>
                @endforeach
        </div>
    </div>
    @endfor
    </div>

    <!-- Toggle button (optional - can be removed if not needed) -->
    <button @click="vertical = !vertical"
        class="hidden fixed top-4 left-4 w-12 h-12 bg-gray-200 rounded-full z-10 items-center justify-center hover:bg-gray-300 transition-colors"
        :class="{ 'rotate-90': vertical }">
        <svg aria-hidden="true" viewBox="0 0 512 512" width="24" class="fill-current">
            <path
                d="M377.941 169.941V216H134.059v-46.059c0-21.382-25.851-32.09-40.971-16.971L7.029 239.029c-9.373 9.373-9.373 24.568 0 33.941l86.059 86.059c15.119 15.119 40.971 4.411 40.971-16.971V296h243.882v46.059c0 21.382 25.851 32.09 40.971 16.971l86.059-86.059c9.373-9.373 9.373-24.568 0-33.941l-86.059-86.059c-15.119-15.12-40.971-4.412-40.971 16.97z" />
        </svg>
    </button>
    </div>
    </div>

    @pushOnce('styles','marquee-style')
    <link rel="stylesheet" href="{{ asset('css/blocks/marquee.css') }}">
    @endpushOnce
</x-block>