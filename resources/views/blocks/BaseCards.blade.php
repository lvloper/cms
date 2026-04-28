@php
    $descriptionHtml = $description ?? '';

    if (! is_string($descriptionHtml) && ! $descriptionHtml instanceof \Stringable) {
        $descriptionHtml = '';
    }
@endphp

<x-block class="py-12 md:py-20">
    <div class="container mx-auto">
        @if(!empty($title))
            <h2 class="max-w-4xl text-3xl font-bold leading-tight text-gray-950 md:text-5xl">{{ $title }}</h2>
        @endif

        @if(!empty($descriptionHtml))
            <div class="mt-4 max-w-3xl text-lg leading-relaxed text-gray-700">{!! $descriptionHtml !!}</div>
        @endif

        <div class="mt-10 grid gap-6 md:grid-cols-3">
            @foreach(($items ?? []) as $item)
                <article class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-md">
                    @if(!empty($item['image']))
                        <x-image :image="$item['image']" class="aspect-[4/3] bg-gray-100" imageClass="h-full w-full object-cover" :alt="$item['title'] ?? ''" />
                    @endif

                    <div class="p-6">
                        <h3 class="text-xl font-bold text-gray-950">{{ $item['title'] ?? '' }}</h3>
                        @if(!empty($item['description']))
                            <p class="mt-3 leading-relaxed text-gray-700">{{ is_array($item['description']) ? '' : $item['description'] }}</p>
                        @endif

                        <x-link :attrs="$item['route'] ?? []" :hideIfNull="true" class="mt-5 inline-flex font-semibold text-primary hover:underline">
                            Ver más
                        </x-link>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</x-block>
