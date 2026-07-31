@php($media = $media ?? [])
@php($textOrder = ($layout ?? 'text_left') === 'text_right' ? 'md:order-2 md:col-start-9' : 'md:order-1')
@php($mediaOrder = ($layout ?? 'text_left') === 'text_right' ? 'md:order-1 md:col-start-1' : 'md:order-2')

<x-block class="border-b border-white/20 py-12 md:py-16">
    <div class="container mx-auto grid gap-10 md:grid-cols-12 md:gap-8">
        <div class="md:col-span-4 {{ $textOrder }}">
            <div class="md:sticky md:top-28">
                @if($eyebrow ?? false)<p class="mb-4 text-xs font-bold tracking-widest text-socies-green">{{ $eyebrow }}</p>@endif
                @if($title ?? false)<h2 class="text-3xl font-bold md:text-5xl">{{ $title }}</h2>@endif
                @if($body ?? false)<div class="client-case-rich mt-6 text-gray-2">{!! $body !!}</div>@endif
                @if($outcome ?? false)<p class="mt-8 border-t border-socies-green pt-5 text-lg font-bold">{{ $outcome }}</p>@endif
            </div>
        </div>
        <div class="grid gap-6 md:col-span-8 md:grid-cols-2 md:gap-8 {{ $mediaOrder }}">
            @foreach($media as $index => $item)
                <div class="{{ $index === 0 ? 'md:col-span-2' : '' }}">
                    <x-client.case-media
                        :type="$item['media_type'] ?? 'image'"
                        :image="$item['media_image'] ?? null"
                        :video="$item['media_video'] ?? null"
                        :alt="$item['media_alt'] ?? ''"
                        :placeholder="$item['media_placeholder'] ?? null"
                        :autoplay="$item['media_autoplay'] ?? false"
                        class="{{ $index === 0 ? 'aspect-video' : 'aspect-square' }}"
                    />
                </div>
            @endforeach
        </div>
    </div>
</x-block>
