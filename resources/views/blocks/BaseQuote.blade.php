<x-block class="py-12 md:py-20">
    <div class="container mx-auto">
        <figure class="grid gap-8 rounded-3xl bg-gray-950 p-8 text-white md:grid-cols-[220px_1fr] md:p-12">
            @if(!empty($image))
                <x-image :image="$image" class="aspect-square overflow-hidden rounded-2xl bg-white/10" imageClass="h-full w-full object-cover" :alt="$author ?? ''" />
            @endif

            <div>
                <blockquote class="text-2xl font-bold leading-tight md:text-4xl">“{{ $quote ?? '' }}”</blockquote>
                @if(!empty($author) || !empty($source))
                    <figcaption class="mt-6 text-lg text-white/80">
                        @if(!empty($author))<strong class="text-white">{{ $author }}</strong>@endif
                        @if(!empty($source))<span>{{ !empty($author) ? ' · ' : '' }}{{ $source }}</span>@endif
                    </figcaption>
                @endif
            </div>
        </figure>
    </div>
</x-block>
