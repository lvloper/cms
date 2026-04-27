@php
    $descriptionHtml = is_array($description ?? null) ? tiptap_converter()->asHTML($description) : ($description ?? '');
@endphp

<x-block class="py-12 md:py-20">
    <div class="container mx-auto max-w-5xl">
        @if(!empty($title))
            <h2 class="text-3xl font-bold leading-tight text-gray-950 md:text-5xl">{{ $title }}</h2>
        @endif

        @if(!empty($descriptionHtml))
            <div class="mt-4 text-lg leading-relaxed text-gray-700">{!! $descriptionHtml !!}</div>
        @endif

        <div class="mt-8 divide-y divide-gray-200 rounded-2xl border border-gray-200 bg-white">
            @foreach(($items ?? []) as $item)
                <div class="p-5 md:flex md:items-center md:justify-between md:gap-6">
                    <div>
                        <x-link :attrs="$item['route'] ?? []" class="text-xl font-bold text-gray-950 hover:text-primary">
                            {{ $item['route']['btn_label'] ?? 'Enlace' }}
                        </x-link>
                        @if(!empty($item['description']))
                            <p class="mt-2 leading-relaxed text-gray-700">{{ $item['description'] }}</p>
                        @endif
                    </div>
                    <x-link :attrs="$item['route'] ?? []" class="mt-4 inline-flex text-sm font-semibold uppercase tracking-wide text-primary md:mt-0">
                        Abrir
                    </x-link>
                </div>
            @endforeach
        </div>
    </div>
</x-block>
