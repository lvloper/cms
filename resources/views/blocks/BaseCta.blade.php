@php
    $variant = $variant ?? 'accent';
    $classes = match ($variant) {
        'dark' => 'bg-gray-950 text-white',
        'light' => 'bg-gray-50 text-gray-950',
        default => 'bg-primary text-white',
    };
    $buttonClass = $variant === 'light'
        ? 'inline-flex rounded-full bg-primary px-6 py-3 font-semibold text-white transition hover:opacity-90'
        : 'inline-flex rounded-full bg-white px-6 py-3 font-semibold text-primary transition hover:opacity-90';
    $descriptionHtml = $description ?? '';

    if (! is_string($descriptionHtml) && ! $descriptionHtml instanceof \Stringable) {
        $descriptionHtml = '';
    }
@endphp

<x-block class="py-12 md:py-20">
    <div class="container mx-auto">
        <div class="rounded-3xl px-6 py-10 md:px-12 md:py-14 {{ $classes }}">
            @if(!empty($eyebrow))
                <p class="mb-3 text-sm font-semibold uppercase tracking-[0.2em] opacity-80">{{ $eyebrow }}</p>
            @endif

            <div class="grid gap-8 md:grid-cols-[1fr_auto] md:items-end">
                <div>
                    <h2 class="text-3xl font-bold leading-tight md:text-5xl">{{ $title ?? '' }}</h2>
                    @if(!empty($descriptionHtml))
                        <div class="mt-4 max-w-3xl text-lg leading-relaxed opacity-90">{!! $descriptionHtml !!}</div>
                    @endif
                </div>

                <div class="flex flex-wrap gap-3">
                    <x-link :attrs="$primary_route ?? []" :hideIfNull="true" class="{{ $buttonClass }}">
                        {{ $primary_route['btn_label'] ?? 'Ver más' }}
                    </x-link>
                    <x-link :attrs="$secondary_route ?? []" :hideIfNull="true" class="inline-flex rounded-full border px-6 py-3 font-semibold transition hover:opacity-80">
                        {{ $secondary_route['btn_label'] ?? 'Contactar' }}
                    </x-link>
                </div>
            </div>
        </div>
    </div>
</x-block>
