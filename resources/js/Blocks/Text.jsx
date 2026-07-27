import { TiptapContent } from '@/Lib/tiptap'

export default function TextBlock({ eyebrow, title, content, width = 'container' }) {
    const widthClass = {
        narrow: 'max-w-3xl',
        wide: 'max-w-7xl',
        container: 'max-w-5xl',
    }[width] ?? 'max-w-5xl'

    return (
        <section className="py-12 md:py-16">
            <div className="container mx-auto px-4">
                <div className={`mx-auto ${widthClass}`}>
                    {eyebrow && (
                        <p className="text-sm font-semibold tracking-widest uppercase text-gray-500 mb-3">{eyebrow}</p>
                    )}
                    {title && (
                        <h2 className="text-3xl md:text-4xl font-bold text-gray-900 mb-6">{title}</h2>
                    )}
                    <TiptapContent content={content} />
                </div>
            </div>
        </section>
    )
}
