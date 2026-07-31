import { TiptapContent } from '@/Lib/tiptap'
import CaseMedia from '@/Components/ClientCase/CaseMedia'

export default function ClientStatement({ eyebrow, title, body, layout = 'text_left', media_type, media_image, media_video, media_alt, media_placeholder, media_autoplay }) {
    const textOrder = layout === 'text_right' ? 'md:order-2 md:col-start-6' : 'md:order-1'
    const mediaOrder = layout === 'text_right' ? 'md:order-1 md:col-start-1' : 'md:order-2'

    return (
        <section className="border-b border-white/20 py-12 md:py-16" data-case-block>
            <div className="container mx-auto">
                <div className="grid items-center gap-8 md:grid-cols-12 md:gap-8">
                    <div className={`md:col-span-7 ${textOrder}`} data-client-reveal>
                        {eyebrow && <p className="mb-6 text-xs font-bold tracking-widest text-socies-yellow">{eyebrow}</p>}
                        <h2 className="text-4xl font-bold leading-[1.02] md:text-6xl">{title}</h2>
                        {body && <div className="client-case-rich mt-8 max-w-2xl text-lg text-gray-2"><TiptapContent content={body} className="[&_p]:mb-4 [&_p:last-child]:mb-0 [&_a]:underline [&_ul]:list-disc [&_ul]:pl-5 [&_ol]:list-decimal [&_ol]:pl-5" /></div>}
                    </div>
                    <div className={`relative md:col-span-5 ${mediaOrder}`} data-client-reveal>
                        <span className="absolute -bottom-3 -right-3 z-10 h-12 w-12 rounded-full bg-socies-coral" aria-hidden="true" />
                        <CaseMedia
                            type={media_type}
                            image={media_image}
                            video={media_video}
                            alt={media_alt}
                            placeholder={media_placeholder}
                            autoplay={media_autoplay}
                            className="aspect-[4/3]"
                        />
                    </div>
                </div>
            </div>
        </section>
    )
}
