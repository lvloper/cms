@php($bodyHtml = filled($body ?? null)
    ? \Filament\Forms\Components\RichEditor\RichContentRenderer::make($body)->toHtml()
    : '')
@php($textOrder = ($layout ?? 'text_left') === 'text_right' ? 'md:order-2 md:col-start-6' : 'md:order-1')
@php($mediaOrder = ($layout ?? 'text_left') === 'text_right' ? 'md:order-1 md:col-start-1' : 'md:order-2')

<x-block class="border-b border-white/20 py-12 md:py-16">
    <div class="container mx-auto grid items-center gap-8 md:grid-cols-12">
        <div class="md:col-span-7 {{ $textOrder }}">
            @if($eyebrow ?? false)<p class="mb-6 text-xs font-bold tracking-widest text-socies-yellow">{{ $eyebrow }}</p>@endif
            @if($title ?? false)<h2 class="text-4xl font-bold leading-tight md:text-6xl">{{ $title }}</h2>@endif
            @if($bodyHtml)<div class="client-case-rich mt-8 max-w-2xl text-gray-2">{!! $bodyHtml !!}</div>@endif
        </div>
        <div class="md:col-span-5 {{ $mediaOrder }}">
            <x-client.case-media
                :type="$media_type ?? 'image'"
                :image="$media_image ?? null"
                :video="$media_video ?? null"
                :alt="$media_alt ?? ''"
                :placeholder="$media_placeholder ?? null"
                :autoplay="$media_autoplay ?? false"
                class="aspect-[4/3]"
            />
        </div>
    </div>
</x-block>
