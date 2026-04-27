@php
    $descriptionHtml = is_array($description ?? null) ? tiptap_converter()->asHTML($description) : ($description ?? '');
@endphp

<x-block class="py-12 md:py-20">
    <div class="container mx-auto">
        @if(!empty($title))
            <h2 class="max-w-4xl text-3xl font-bold leading-tight text-gray-950 md:text-5xl">{{ $title }}</h2>
        @endif

        @if(!empty($descriptionHtml))
            <div class="mt-4 max-w-3xl text-lg leading-relaxed text-gray-700">{!! $descriptionHtml !!}</div>
        @endif

        <div class="mt-10 grid gap-4 md:grid-cols-3">
            @foreach(($items ?? []) as $item)
                <div class="rounded-2xl bg-gray-50 p-6 md:p-8">
                    <div class="text-4xl font-black tracking-tight text-primary md:text-6xl">{{ $item['value'] ?? '' }}</div>
                    <div class="mt-3 text-xl font-bold text-gray-950">{{ $item['label'] ?? '' }}</div>
                    @if(!empty($item['description']))
                        <p class="mt-2 leading-relaxed text-gray-700">{{ $item['description'] }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</x-block>
