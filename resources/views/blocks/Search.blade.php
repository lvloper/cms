@php
    $searchTerm = '';
    $path = request()->path();
    $segments = array_filter(explode('/', $path));
    $lastSegment = end($segments);
    if ($lastSegment && $lastSegment !== '/' && strlen($lastSegment) > 2) {
        $searchTerm = str_replace(['-', '_'], ' ', $lastSegment);
    }
@endphp

<x-block class="py-12 md:py-16">
    <div class="container mx-auto px-4">
        <div class="mx-auto max-w-2xl text-center">
            @if ($title ?? null)
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">{{ $title }}</h2>
            @endif

            @if ($description ?? null)
                <p class="text-lg text-gray-600 mb-8">{{ $description }}</p>
            @endif

            <livewire:search-component
                :isFullPage="true"
                :autoSearch="!empty($searchTerm)"
                :initialSearch="$searchTerm"
            />
        </div>
    </div>
</x-block>
