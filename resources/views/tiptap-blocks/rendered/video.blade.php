@php
    $videoType = $videoType ?? 'youtube';
    $style = $style ?? 'default';
    $containerClass = $style === 'compact' ? 'max-w-2xl mx-auto' : 'w-full';
@endphp

<div class="my-8 {{ $containerClass }}">
    @if($videoType === 'youtube' && !empty($videoId))
        <div class="aspect-video w-full">
            <lite-youtube
                videoid="{{ $videoId }}"
                class="w-full h-full"
                playlabel="Reproducir video">
            </lite-youtube>
        </div>
    @elseif($videoType === 'upload' && !empty($videoFile))
        <video
            controls
            class="w-full aspect-video rounded-lg bg-black"
            preload="metadata"
        >
            <source src="{{ Storage::url($videoFile) }}" type="video/mp4">
            Tu navegador no soporta el elemento de video.
        </video>
    @endif
</div>
