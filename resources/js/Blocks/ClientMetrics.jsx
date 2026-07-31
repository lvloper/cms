import CaseMedia from '@/Components/ClientCase/CaseMedia'
import SectionIntro from '@/Components/ClientCase/SectionIntro'

export default function ClientMetrics({ eyebrow, title, body, layout = 'text_left', metrics = [], media_type, media_image, media_video, media_alt, media_placeholder, media_autoplay }) {
    const textOrder = layout === 'text_right' ? 'md:order-2 md:col-start-8' : 'md:order-1'
    const mediaOrder = layout === 'text_right' ? 'md:order-1 md:col-start-1' : 'md:order-2'

    return (
        <section className="border-b border-white/20 py-12 md:py-16" data-case-block>
            <div className="container mx-auto">
                <div className="grid gap-10 md:grid-cols-12 md:gap-8">
                    <SectionIntro eyebrow={eyebrow} title={title} body={body} className={`md:col-span-5 ${textOrder}`} />
                    <div className={`md:col-span-7 ${mediaOrder}`} data-client-reveal>
                        <CaseMedia
                            type={media_type}
                            image={media_image}
                            video={media_video}
                            alt={media_alt}
                            placeholder={media_placeholder}
                            autoplay={media_autoplay}
                            className="aspect-video"
                        />
                    </div>
                </div>
                <dl className="mt-10 grid border-t border-white/20 md:mt-14 md:grid-cols-4">
                    {metrics.map((metric, index) => (
                        <div key={`${metric.value}-${index}`} className="border-b border-white/20 py-6 md:border-b-0 md:border-r md:px-6 md:first:pl-0 md:last:border-r-0" data-client-reveal>
                            <dt className="text-sm leading-snug text-gray-2">{metric.label}</dt>
                            <dd className="mt-3 text-4xl font-bold text-socies-yellow md:text-5xl" aria-label={metric.value}>
                                <span aria-hidden="true" data-metric-counter data-metric-value={metric.value}>{metric.value}</span>
                            </dd>
                            {metric.note && <p className="mt-3 text-xs text-gray-2">{metric.note}</p>}
                        </div>
                    ))}
                </dl>
            </div>
        </section>
    )
}
