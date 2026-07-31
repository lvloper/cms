export default function ClientMarquee({ items = [], speed = 'slow', direction = 'left' }) {
    if (items.length === 0) return null

    const content = items.map((item, index) => (
        <span key={`${item}-${index}`} className="flex shrink-0 items-center gap-6 md:gap-10">
            <span>{item}</span>
            <span className="h-3 w-3 shrink-0 rounded-full bg-socies-yellow md:h-4 md:w-4" aria-hidden="true" />
        </span>
    ))

    return (
        <section className="overflow-hidden border-b border-white/20 py-8 md:py-12" data-case-block aria-label={items.join(', ')}>
            <div className={`client-marquee client-marquee--${speed} client-marquee--${direction}`}>
                <div className="client-marquee__track text-3xl font-bold md:text-5xl" data-client-marquee>
                    <div className="flex shrink-0 gap-6 pr-6 md:gap-10 md:pr-10">{content}</div>
                    <div className="flex shrink-0 gap-6 pr-6 md:gap-10 md:pr-10" aria-hidden="true">{content}</div>
                </div>
            </div>
        </section>
    )
}
