@php
    $mediaType = $media_type ?? 'image';
@endphp

<x-block class="py-12 md:py-16">
    <div class="container mx-auto px-4">
        <div class="mx-auto max-w-5xl">
            @if ($mediaType === 'youtube' && ($youtube_id ?? null))
                <div class="relative aspect-video overflow-hidden rounded-lg bg-gray-100">
                    <iframe
                        src="https://www.youtube-nocookie.com/embed/{{ $youtube_id }}"
                        title="YouTube video"
                        class="absolute inset-0 h-full w-full"
                        frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen
                    ></iframe>
                </div>
            @elseif ($mediaType === 'upload' && ($video_file ?? null))
                <div class="relative aspect-video overflow-hidden rounded-lg bg-gray-100">
                    <video
                        controls
                        class="h-full w-full"
                        preload="metadata"
                    >
                        <source src="{{ \Illuminate\Support\Facades\Storage::url($video_file) }}" type="video/mp4">
                    </video>
                </div>
            @elseif ($mediaType === 'image' && ($image ?? null))
                <div class="overflow-hidden rounded-lg bg-gray-100">
                    <img
                        src="{{ \Illuminate\Support\Facades\Storage::url($image) }}"
                        alt="{{ $caption ?? '' }}"
                        class="h-full w-full object-cover"
                    >
                </div>
            @endif

            @if ($caption ?? null)
                <p class="mt-3 text-sm text-gray-500 text-center">{{ $caption }}</p>
            @endif
        </div>
    </div>
</x-block>
