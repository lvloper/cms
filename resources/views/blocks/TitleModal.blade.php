@php
    $colorClass = match($color ?? 'primary') {
        'black' => 'text-black',
        'secondary' => 'text-secondary',
        'primary' => 'text-primary',
        default => 'text-primary',
    };
@endphp

<x-block>
    <h2 class="mb-6 font-bold leading-tight sm:text-xl 4xl:text-3xl {{ $colorClass }}">
        {{ $title ?? ''}}
    </h2>
</x-block>