@php($testimonials = $testimonials ?? [])

<x-block class="border-b border-white/20 py-12 md:py-16">
    <div class="container mx-auto">
        @if($eyebrow ?? false)<p class="mb-4 text-xs font-bold tracking-widest text-socies-green">{{ $eyebrow }}</p>@endif
        @if($title ?? false)<h2 class="text-3xl font-bold md:text-5xl">{{ $title }}</h2>@endif
        <div class="mt-10 grid gap-10 md:grid-cols-2 md:gap-8">
            @foreach($testimonials as $testimonial)
                <figure class="grid gap-6 border-t border-white/30 pt-6 sm:grid-cols-[8rem_1fr]">
                    <x-client.case-media
                        :type="$testimonial['media_type'] ?? 'image'"
                        :image="$testimonial['media_image'] ?? null"
                        :video="$testimonial['media_video'] ?? null"
                        :alt="$testimonial['media_alt'] ?? ''"
                        :placeholder="$testimonial['media_placeholder'] ?? null"
                        :autoplay="$testimonial['media_autoplay'] ?? false"
                        class="aspect-square"
                    />
                    <div>
                        <blockquote class="text-xl font-bold md:text-2xl">{{ $testimonial['quote'] ?? '' }}</blockquote>
                        <figcaption class="mt-6 border-t border-white/20 pt-4 text-sm">
                            <strong class="block">{{ $testimonial['person'] ?? '' }}</strong>
                            <span class="text-gray-2">{{ $testimonial['role'] ?? '' }}</span>
                        </figcaption>
                    </div>
                </figure>
            @endforeach
        </div>
    </div>
</x-block>
