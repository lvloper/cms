@php($nodes = $nodes ?? [])

<x-block class="border-b border-white/20 py-12 md:py-16">
    <div class="container mx-auto grid gap-10 md:grid-cols-12 md:gap-8">
        <div class="md:col-span-4">
            @if($eyebrow ?? false)<p class="mb-4 text-xs font-bold tracking-widest text-socies-green">{{ $eyebrow }}</p>@endif
            @if($title ?? false)<h2 class="text-3xl font-bold md:text-5xl">{{ $title }}</h2>@endif
            @if($body ?? false)<p class="mt-5 text-gray-2">{{ $body }}</p>@endif
        </div>
        <ol class="client-process__grid md:col-span-8">
            @foreach($nodes as $index => $node)
                <li class="client-process__node">
                    <span class="client-process__index">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span>
                    <h3 class="text-lg font-bold md:text-xl">{{ $node['label'] ?? '' }}</h3>
                    @if($node['detail'] ?? false)<p class="mt-2 text-sm text-gray-2">{{ $node['detail'] }}</p>@endif
                </li>
            @endforeach
        </ol>
    </div>
</x-block>
