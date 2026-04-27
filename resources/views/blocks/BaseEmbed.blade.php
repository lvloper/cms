<x-block class="py-12 md:py-20">
    <div class="container mx-auto max-w-5xl">
        @if(!empty($title))
            <h2 class="mb-6 text-3xl font-bold leading-tight text-gray-950 md:text-5xl">{{ $title }}</h2>
        @endif

        <div class="overflow-hidden rounded-2xl bg-gray-100 [&_iframe]:aspect-video [&_iframe]:h-auto [&_iframe]:w-full">
            {!! $embed ?? '' !!}
        </div>

        @if(!empty($caption))
            <p class="mt-3 text-sm text-gray-500">{{ $caption }}</p>
        @endif
    </div>
</x-block>
