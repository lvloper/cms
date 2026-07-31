@props([
    'type' => 'image',
    'image' => null,
    'video' => null,
    'alt' => '',
    'placeholder' => null,
    'autoplay' => false,
    'class' => '',
])

@php
    $resolveMedia = static function ($value): ?string {
        if (! is_string($value) || blank($value)) {
            return null;
        }

        return str_starts_with($value, 'http') || str_starts_with($value, '/')
            ? $value
            : \Illuminate\Support\Facades\Storage::url($value);
    };

    $imageUrl = $resolveMedia($image);
    $videoUrl = $resolveMedia($video);
    $placeholderCopy = $placeholder ?: 'Reemplazar por imagen/video de este contenido';
    $placeholderCopy = str_starts_with(mb_strtolower($placeholderCopy), 'reemplazar por')
        ? $placeholderCopy
        : 'Reemplazar por imagen/video de '.$placeholderCopy;
@endphp

<figure {{ $attributes->merge(['class' => 'client-case-media '.$class]) }}>
    @if($type === 'image' && $imageUrl)
        <img src="{{ $imageUrl }}" alt="{{ $alt }}" class="h-full w-full object-cover" loading="lazy">
    @elseif($type === 'video' && $videoUrl)
        <video
            class="h-full w-full object-cover"
            aria-label="{{ $alt ?: 'Video del caso de cliente' }}"
            controls
            playsinline
            preload="metadata"
            @if($autoplay) autoplay muted loop @endif
        >
            <source src="{{ $videoUrl }}">
        </video>
    @else
        <div class="flex h-full min-h-64 items-center justify-center bg-gray p-6 text-center text-white" role="img" aria-label="{{ $alt ?: $placeholderCopy }}">
            <span class="max-w-sm text-sm font-bold leading-relaxed">{{ $placeholderCopy }}</span>
        </div>
    @endif
</figure>
