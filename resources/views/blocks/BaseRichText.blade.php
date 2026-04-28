@php
    $widthClass = match ($width ?? 'container') {
        'narrow' => 'max-w-3xl',
        'wide' => 'max-w-7xl',
        default => 'max-w-5xl',
    };
    $contentHtml = $content ?? '';

    if ($contentHtml instanceof \Illuminate\Contracts\Support\Arrayable) {
        $contentHtml = $contentHtml->toArray();
    }

    if (! is_string($contentHtml) && ! $contentHtml instanceof \Stringable) {
        $contentHtml = '';
    }
@endphp

<x-block class="py-12 md:py-20">
    <div class="container mx-auto {{ $widthClass }}">
        @if(!empty($eyebrow))
            <p class="mb-3 text-sm font-semibold uppercase tracking-[0.2em] text-primary">{{ $eyebrow }}</p>
        @endif

        @if(!empty($title))
            <h2 class="mb-6 text-3xl font-bold leading-tight text-gray-950 md:text-5xl">{{ $title }}</h2>
        @endif

        <div class="prose max-w-none text-lg leading-relaxed text-gray-700">
            {!! $contentHtml !!}
        </div>
    </div>
</x-block>
