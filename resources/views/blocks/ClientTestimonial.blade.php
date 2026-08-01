@php($testimonial = collect($testimonials ?? [])->first())

@if($testimonial)
    @php($initials = collect(preg_split('/\s+/', trim($testimonial['person'] ?? '')))->filter()->map(fn ($name) => mb_substr($name, 0, 1))->take(2)->join(''))

    <x-block class="border-b border-white/20 py-12 md:py-16">
        <div class="container mx-auto max-w-5xl">
            @if($eyebrow ?? false)<p class="mb-4 text-xs font-bold tracking-widest text-socies-green">{{ $eyebrow }}</p>@endif
            @if($title ?? false)<h2 class="text-3xl font-bold md:text-5xl">{{ $title }}</h2>@endif
            <figure class="{{ ($eyebrow ?? false) || ($title ?? false) ? 'mt-10 md:mt-14' : '' }} text-center">
                <svg class="mx-auto h-7 w-7 text-socies-green" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21c3 0 5-1 5-5V8H3v8h3c0 2-1 3-3 3v2Zm13 0c3 0 5-1 5-5V8h-5v8h3c0 2-1 3-3 3v2Z"/></svg>
                <blockquote class="mx-auto mt-5 max-w-4xl whitespace-pre-line text-lg font-light leading-relaxed md:text-xl">&ldquo;{{ $testimonial['quote'] ?? '' }}&rdquo;</blockquote>
                <figcaption class="mx-auto mt-8 flex w-fit items-center gap-3 text-left">
                    <span class="flex size-12 shrink-0 items-center justify-center rounded-full border border-white/30 text-sm font-bold" aria-hidden="true">{{ $initials }}</span>
                    <span class="text-sm md:text-base">
                        <strong class="block">{{ $testimonial['person'] ?? '' }}</strong>
                        <span class="mt-1 block text-gray-2">{{ $testimonial['role'] ?? '' }}</span>
                    </span>
                </figcaption>
            </figure>
        </div>
    </x-block>
@endif
