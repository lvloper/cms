import Image from '@/Components/Image'

export default function MediaBlock({ media_type: mediaType = 'image', youtube_id: youtubeId, video_file: videoFile, image, caption }) {
    return (
        <section className="py-12 md:py-16">
            <div className="container mx-auto px-4">
                <div className="mx-auto max-w-5xl">
                    {mediaType === 'youtube' && youtubeId && (
                        <div className="relative aspect-video overflow-hidden rounded-lg bg-gray-100">
                            <iframe
                                src={`https://www.youtube-nocookie.com/embed/${youtubeId}`}
                                title="YouTube video"
                                className="absolute inset-0 h-full w-full"
                                frameBorder="0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowFullScreen
                            />
                        </div>
                    )}
                    {mediaType === 'upload' && videoFile && (
                        <div className="relative aspect-video overflow-hidden rounded-lg bg-gray-100">
                            <video controls className="h-full w-full" preload="metadata">
                                <source src={videoFile} type="video/mp4" />
                            </video>
                        </div>
                    )}
                    {mediaType === 'image' && image && (
                        <div className="overflow-hidden rounded-lg bg-gray-100">
                            <img src={image} alt={caption ?? ''} className="h-full w-full object-cover" />
                        </div>
                    )}
                    {caption && (
                        <p className="mt-3 text-sm text-gray-500 text-center">{caption}</p>
                    )}
                </div>
            </div>
        </section>
    )
}
