@php($media = $media ?? [])

<x-block class="py-12 md:py-16">
    <div class="container mx-auto">
        <div class="client-closing__wall">
            @foreach($media as $index => $item)
                <x-client.case-media
                    :type="$item['media_type'] ?? 'image'"
                    :image="$item['media_image'] ?? null"
                    :video="$item['media_video'] ?? null"
                    :alt="$item['media_alt'] ?? ''"
                    :placeholder="$item['media_placeholder'] ?? null"
                    :autoplay="$item['media_autoplay'] ?? false"
                    class="{{ $index % 3 === 0 ? 'client-closing__wide' : 'aspect-square' }}"
                />
            @endforeach
        </div>
        <div class="mt-12 grid gap-8 border-t border-white/30 pt-8 md:grid-cols-12 md:items-end">
            <div class="md:col-span-8">
                @if($eyebrow ?? false)<p class="mb-4 text-xs font-bold tracking-widest text-socies-green">{{ $eyebrow }}</p>@endif
                @if($title ?? false)<h2 class="text-3xl font-bold md:text-5xl">{{ $title }}</h2>@endif
                @if($body ?? false)<p class="mt-5 max-w-2xl text-gray-2">{{ $body }}</p>@endif
            </div>
            @if($cta ?? false)
                <div class="md:col-span-4 md:text-right">
                    <x-link :attrs="$cta" hideIfNull="true" class="inline-flex border-b border-socies-green pb-3 text-xl font-bold">
                        {{ $cta['btn_label'] ?? 'Hablemos' }}
                    </x-link>
                </div>
            @endif
        </div>
    </div>
</x-block>
