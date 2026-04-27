@props(['style'=>'default'])
@php
$isPreview = isset($preview);

// Auto-detectar tipo de video si no está definido (retrocompatibilidad)
if (!isset($videoType) || empty($videoType)) {
    if (!empty($videoId)) {
        $videoType = 'youtube';
    } elseif (!empty($videoFile)) {
        $videoType = 'upload';
    } else {
        $videoType = 'youtube'; // Default fallback
    }
} else {
    $videoType = $videoType ?? 'youtube';
}

if ($videoType === 'youtube' && isset($videoId)) {
    if (preg_match('/(?:youtube\.com\/(?:[^\/\n\s]+\/\s*(?:\w*\/)*|(?:v|e(?:mbed)?)\/|\w*v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', 
    $videoId, $matches)) {
        $videoId = $matches[1];
    }
}
@endphp
<x-block id="{{ $id }}" class="relative">
    {{-- Vista por defecto --}}
    <div class="{{ $style }} mx-auto w-full video-animate">
        <div class="{{ $style == 'compact' ? 'pt-10 pb-12 max-w-[950px]' : '' }}">
            @if($videoType === 'youtube' && !empty($videoId))
                <lite-youtube videoid='{{ $videoId }}'></lite-youtube>
            @elseif($videoType === 'upload' )
                @php
                    // Si es un array, tomamos el primer elemento (puede ser múltiple upload)
                    $videoPath = is_array($videoFile) ? ($videoFile[0] ?? null) : $videoFile;
                @endphp
                @if($videoPath)
                    <video class="w-full" controls>
                        <source src="{{ Storage::url($videoPath) }}" type="video/mp4">
                        Tu navegador no soporta la reproducción de videos HTML5.
                    </video>
                @endif
            @endif
        </div>
    </div>
</x-block>