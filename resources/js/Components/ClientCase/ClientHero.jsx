import CaseMedia from './CaseMedia'
import ClientOrnaments from './ClientOrnaments'

export default function ClientHero({ client }) {
    const title = client?.hero_title || client?.title
    const services = client?.hero_services ?? []

    return (
        <header className="client-case-hero border-b border-white/20" data-client-hero>
            <ClientOrnaments />
            <div className="container mx-auto grid min-h-[calc(100svh-var(--header-height))] items-end gap-8 py-12 md:grid-cols-12 md:gap-8 md:py-16">
                <div className="md:col-span-7 md:pb-8">
                    <div className="flex flex-wrap items-center gap-x-5 gap-y-3" data-client-hero-item>
                        {client?.logo && (
                            <img
                                src={client.logo}
                                alt={`Logo de ${client.title}`}
                                className="h-10 w-auto max-w-48 object-contain object-left md:h-14 md:max-w-64"
                            />
                        )}
                        <span className="h-1 w-1 rounded-full bg-socies-coral" aria-hidden="true" />
                        <p className="text-xs font-bold tracking-widest text-gray-2">
                            {client?.hero_eyebrow || 'Caso de cliente'}
                        </p>
                    </div>

                    <h1 className="mt-8 max-w-5xl text-4xl font-bold leading-[0.98] md:text-6xl lg:text-7xl" data-client-hero-item>
                        {title}
                    </h1>

                    {client?.hero_summary && (
                        <p className="mt-8 max-w-3xl text-lg leading-relaxed text-gray-2 md:text-2xl" data-client-hero-item>
                            {client.hero_summary}
                        </p>
                    )}

                    <div className="mt-10 flex flex-col gap-6 border-t border-white/20 pt-6 md:flex-row md:items-start md:justify-between" data-client-hero-item>
                        {client?.relationship_since && (
                            <p className="text-sm font-bold text-socies-yellow">{client.relationship_since}</p>
                        )}
                        {services.length > 0 && (
                            <ul className="flex max-w-2xl flex-wrap gap-x-5 gap-y-2 text-sm text-gray-2" aria-label="Capacidades aplicadas">
                                {services.map((service) => <li key={service}>{service}</li>)}
                            </ul>
                        )}
                    </div>
                </div>

                <div className="relative md:col-span-5" data-client-hero-media>
                    <CaseMedia
                        type={client?.hero_media_type}
                        image={client?.hero_media_image}
                        video={client?.hero_media_video}
                        alt={client?.hero_media_alt}
                        placeholder={client?.hero_media_placeholder || `presentación del trabajo con ${client?.title}`}
                        autoplay={client?.hero_media_autoplay}
                        className="aspect-[4/5]"
                        priority
                    />
                </div>
            </div>
        </header>
    )
}
