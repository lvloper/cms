import { TiptapContent } from '@/Lib/tiptap'
import CaseMedia from '@/Components/ClientCase/CaseMedia'
import SectionIntro from '@/Components/ClientCase/SectionIntro'

export default function ClientFeature({ eyebrow, title, body, outcome, layout = 'text_left', media = [] }) {
    const textOrder = layout === 'text_right' ? 'md:order-2 md:col-start-9' : 'md:order-1'
    const mediaOrder = layout === 'text_right' ? 'md:order-1 md:col-start-1' : 'md:order-2'

    return (
        <section className="border-b border-white/20 py-12 md:py-16" data-case-block>
            <div className="container mx-auto grid gap-10 md:grid-cols-12 md:gap-8">
                <div className={`md:col-span-4 ${textOrder}`}>
                    <div className="md:sticky md:top-28">
                        <SectionIntro eyebrow={eyebrow} title={title} />
                        {body && <div className="client-case-rich mt-6 text-gray-2" data-client-reveal><TiptapContent content={body} className="[&_p]:mb-4 [&_p:last-child]:mb-0 [&_a]:underline [&_ul]:list-disc [&_ul]:pl-5 [&_ol]:list-decimal [&_ol]:pl-5" /></div>}
                        {outcome && <p className="mt-8 border-t border-socies-green pt-5 text-lg font-bold leading-snug" data-client-reveal>{outcome}</p>}
                    </div>
                </div>
                <div className={`grid gap-6 md:col-span-8 md:grid-cols-2 md:gap-8 ${mediaOrder}`}>
                    {media.map((item, index) => (
                        <div key={`${item.label || 'feature'}-${index}`} className={index === 0 ? 'md:col-span-2' : ''} data-client-reveal>
                            <CaseMedia
                                type={item.media_type}
                                image={item.media_image}
                                video={item.media_video}
                                alt={item.media_alt}
                                placeholder={item.media_placeholder}
                                autoplay={item.media_autoplay}
                                className={index === 0 ? 'aspect-video' : 'aspect-square'}
                            />
                            {item.caption && <p className="mt-3 text-xs text-gray-2">{item.caption}</p>}
                        </div>
                    ))}
                </div>
            </div>
        </section>
    )
}
