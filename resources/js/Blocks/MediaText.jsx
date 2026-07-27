import { TiptapContent } from '@/Lib/tiptap'
import Link from '@/Components/Link'

export default function MediaTextBlock({ layout = 'left', media_type: mediaType = 'image', youtube_id: youtubeId, video_file: videoFile, image, title, content, cta }) {
    const isLeft = layout === 'left'

    return (
        <section className="py-12 md:py-16">
            <div className="container mx-auto px-4">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-8 md:gap-12 items-center">
                    <div className={isLeft ? 'md:order-1' : 'md:order-2'}>
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
                                <img src={image} alt={title ?? ''} className="h-full w-full object-cover" />
                            </div>
                        )}
                    </div>

                    <div className={isLeft ? 'md:order-2' : 'md:order-1'}>
                        {title && (
                            <h2 className="text-3xl md:text-4xl font-bold text-gray-900 mb-4">{title}</h2>
                        )}
                        <TiptapContent content={content} />
                        {cta?.url && cta.url !== '#' && (
                            <Link
                                link={cta}
                                className="inline-flex items-center mt-6 px-6 py-3 rounded-lg bg-primary text-white font-semibold hover:bg-primary-hover transition-colors"
                            />
                        )}
                    </div>
                </div>
            </div>
        </section>
    )
}
