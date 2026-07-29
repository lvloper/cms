import { Head } from '@inertiajs/react'
import BlocksList from '@/Components/BlocksList'
import DefaultLayout from '@/Layouts/Default'

function ClientWorks({ works = [] }) {
    if (works.length === 0) return null

    return (
        <section className="container mx-auto px-4 py-12 md:py-16" aria-labelledby="client-works-title">
            <h2 id="client-works-title" className="mb-8 text-3xl font-bold md:text-4xl">Trabajos</h2>
            <ul className="grid grid-cols-1 gap-6 md:grid-cols-2" role="list">
                {works.map((work, index) => (
                    <li key={`${work.title}-${index}`} className="border-t border-white/20 pt-6">
                        <a
                            href={work.external_url}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="group block focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-white"
                        >
                            {work.image && (
                                <img
                                    src={work.image}
                                    alt=""
                                    className="mb-5 aspect-[3/2] w-full object-cover"
                                    loading="lazy"
                                />
                            )}
                            <div className="flex items-start justify-between gap-4">
                                <h3 className="text-xl font-bold md:text-2xl">{work.title}</h3>
                                <span aria-hidden="true" className="text-xl transition-transform group-hover:translate-x-1">↗</span>
                            </div>
                            {work.description && (
                                <p className="mt-3 max-w-2xl text-gray-2">{work.description}</p>
                            )}
                            {work.categories?.length > 0 && (
                                <ul className="mt-4 flex flex-wrap gap-2" aria-label="Categorías">
                                    {work.categories.map((category) => (
                                        <li key={category} className="border border-white/20 px-3 py-1 text-xs uppercase tracking-wide">
                                            {category.replaceAll('-', ' ')}
                                        </li>
                                    ))}
                                </ul>
                            )}
                        </a>
                    </li>
                ))}
            </ul>
        </section>
    )
}

function ClientTestimonials({ testimonials = [] }) {
    if (testimonials.length === 0) return null

    return (
        <section className="container mx-auto px-4 py-12 md:py-16" aria-labelledby="client-testimonials-title">
            <h2 id="client-testimonials-title" className="mb-8 text-3xl font-bold md:text-4xl">Testimonios</h2>
            <ul className="grid grid-cols-1 gap-8 lg:grid-cols-2" role="list">
                {testimonials.map((testimonial, index) => (
                    <li key={`${testimonial.person}-${index}`}>
                            <figure className="border-l border-white/30 pl-6">
                            <blockquote
                                className="prose prose-invert max-w-none text-xl leading-relaxed md:text-2xl"
                                dangerouslySetInnerHTML={{ __html: testimonial.testimonial }}
                            />
                            <figcaption className="mt-6 flex items-center gap-4">
                                {testimonial.image && (
                                    <img
                                        src={testimonial.image}
                                        alt=""
                                        className="h-14 w-14 rounded-full object-cover"
                                        loading="lazy"
                                    />
                                )}
                                <span>
                                    <strong className="block">{testimonial.person}</strong>
                                    <span className="text-gray-2">{testimonial.position}</span>
                                </span>
                            </figcaption>
                        </figure>
                    </li>
                ))}
            </ul>
        </section>
    )
}

export default function Client({ client, blocks = [], route }) {
    return (
        <>
            <Head title={client?.title} />
            <DefaultLayout route={route}>
                <article className="bg-black text-white">
                    <header className="container mx-auto px-4 py-12 md:py-16">
                        <h1 className="sr-only">{client?.title}</h1>
                        {client?.logo && (
                            <img
                                src={client.logo}
                                alt={`Logo de ${client.title}`}
                                className="h-20 w-auto max-w-full object-contain md:h-28"
                            />
                        )}
                    </header>

                    <BlocksList blocks={blocks} />
                    <ClientWorks works={client?.works} />
                    <ClientTestimonials testimonials={client?.testimonials} />
                </article>
            </DefaultLayout>
        </>
    )
}
