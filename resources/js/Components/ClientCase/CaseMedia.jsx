import { Play } from 'lucide-react'

function placeholderCopy(value) {
    if (!value) return 'Reemplazar por imagen/video de este contenido'
    if (value.toLowerCase().startsWith('reemplazar por')) return value

    return `Reemplazar por imagen/video de ${value}`
}

export default function CaseMedia({
    type = 'image',
    image,
    video,
    alt = '',
    placeholder,
    autoplay = false,
    className = '',
    mediaClassName = '',
    priority = false,
}) {
    const hasImage = type === 'image' && image
    const hasVideo = type === 'video' && video

    return (
        <figure className={`client-case-media ${className}`} data-client-media>
            {hasImage && (
                <img
                    src={image}
                    alt={alt}
                    className={`h-full w-full object-cover ${mediaClassName}`}
                    loading={priority ? 'eager' : 'lazy'}
                    fetchPriority={priority ? 'high' : 'auto'}
                />
            )}

            {hasVideo && (
                <video
                    className={`h-full w-full object-cover ${mediaClassName}`}
                    aria-label={alt || 'Video del caso de cliente'}
                    autoPlay={autoplay}
                    muted={autoplay}
                    loop={autoplay}
                    controls
                    playsInline
                    preload="metadata"
                >
                    <source src={video} />
                    Tu navegador no puede reproducir este video.
                </video>
            )}

            {!hasImage && !hasVideo && (
                <div className="flex h-full min-h-64 w-full items-center justify-center bg-gray p-6 text-center text-white">
                    <div className="max-w-sm" role="img" aria-label={alt || placeholderCopy(placeholder)}>
                        <Play className="mx-auto mb-4 h-6 w-6" aria-hidden="true" />
                        <span className="text-sm font-bold leading-relaxed md:text-base">
                            {placeholderCopy(placeholder)}
                        </span>
                    </div>
                </div>
            )}
        </figure>
    )
}
