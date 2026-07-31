@php($metrics = $metrics ?? [])
@php($textOrder = ($layout ?? 'text_left') === 'text_right' ? 'md:order-2 md:col-start-8' : 'md:order-1')
@php($mediaOrder = ($layout ?? 'text_left') === 'text_right' ? 'md:order-1 md:col-start-1' : 'md:order-2')

<x-block class="border-b border-white/20 py-12 md:py-16">
    <div class="container mx-auto">
        <div class="grid gap-10 md:grid-cols-12 md:gap-8">
            <div class="md:col-span-5 {{ $textOrder }}">
                @if($eyebrow ?? false)<p class="mb-4 text-xs font-bold tracking-widest text-socies-green">{{ $eyebrow }}</p>@endif
                @if($title ?? false)<h2 class="text-3xl font-bold md:text-5xl">{{ $title }}</h2>@endif
                @if($body ?? false)<p class="mt-5 text-gray-2">{{ $body }}</p>@endif
            </div>
            <div class="md:col-span-7 {{ $mediaOrder }}">
                <x-client.case-media
                    :type="$media_type ?? 'image'"
                    :image="$media_image ?? null"
                    :video="$media_video ?? null"
                    :alt="$media_alt ?? ''"
                    :placeholder="$media_placeholder ?? null"
                    :autoplay="$media_autoplay ?? false"
                    class="aspect-video"
                />
            </div>
        </div>
        <dl class="mt-10 grid border-t border-white/20 md:grid-cols-4">
            @foreach($metrics as $metric)
                <div class="border-b border-white/20 py-6 md:border-b-0 md:border-r md:px-6">
                    <dt class="text-sm text-gray-2">{{ $metric['label'] ?? '' }}</dt>
                    <dd class="mt-3 text-4xl font-bold text-socies-yellow md:text-5xl" aria-label="{{ $metric['value'] ?? '' }}">
                        <span aria-hidden="true" data-metric-counter data-metric-value="{{ $metric['value'] ?? '' }}">{{ $metric['value'] ?? '' }}</span>
                    </dd>
                    @if($metric['note'] ?? false)<p class="mt-3 text-xs text-gray-2">{{ $metric['note'] }}</p>@endif
                </div>
            @endforeach
        </dl>
    </div>
</x-block>
