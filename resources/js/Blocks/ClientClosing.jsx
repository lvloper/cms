import CaseMedia from '@/Components/ClientCase/CaseMedia'
import SectionIntro from '@/Components/ClientCase/SectionIntro'
import SociesConversation from '@/Components/Paco/SociesConversation'

export default function ClientClosing({ eyebrow, title, body, media = [], client }) {
    const entry = client?.id ? {
        campaign: client.paco_campaign || 'direct_default',
        pageContext: {
            contentType: 'client',
            contentId: client.id,
        },
    } : null

    return (
        <section className="py-12 md:py-16" data-case-block>
            <div className="container mx-auto">
                <div className="client-closing__wall" data-client-reveal>
                    {media.map((item, index) => (
                        <CaseMedia
                            key={`${item.label || 'closing'}-${index}`}
                            type={item.media_type}
                            image={item.media_image}
                            video={item.media_video}
                            alt={item.media_alt}
                            placeholder={item.media_placeholder}
                            autoplay={item.media_autoplay}
                            className={index % 3 === 0 ? 'client-closing__wide' : 'aspect-square'}
                        />
                    ))}
                </div>

                <div className="mt-12 grid gap-8 border-t border-white/30 pt-8 md:mt-16 md:grid-cols-12 md:items-end md:gap-8">
                    <SectionIntro eyebrow={eyebrow} title={title} body={body} className="md:col-span-8" />
                </div>

                {entry && <SociesConversation entry={entry} />}
            </div>
        </section>
    )
}
