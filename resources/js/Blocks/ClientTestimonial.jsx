import { useState } from 'react'
import { Quote } from 'lucide-react'
import SectionIntro from '@/Components/ClientCase/SectionIntro'

const QUOTE_PREVIEW_LENGTH = 420

function quotePreview(quote) {
    if (quote.length <= QUOTE_PREVIEW_LENGTH) return quote

    const preview = quote.slice(0, QUOTE_PREVIEW_LENGTH)
    const lastWordBreak = preview.lastIndexOf(' ')

    return `${preview.slice(0, lastWordBreak > 0 ? lastWordBreak : QUOTE_PREVIEW_LENGTH)}…`
}

export default function ClientTestimonial({ eyebrow, title, testimonials = [] }) {
    const testimonial = testimonials[0]
    const [isExpanded, setIsExpanded] = useState(false)

    if (!testimonial) return null

    const quote = testimonial.quote ?? ''
    const hasLongQuote = quote.length > QUOTE_PREVIEW_LENGTH
    const visibleQuote = isExpanded || !hasLongQuote ? quote : quotePreview(quote)

    const initials = testimonial.person
        ?.split(/\s+/)
        .filter(Boolean)
        .map((name) => name[0])
        .slice(0, 2)
        .join('')
        .toUpperCase()

    return (
        <section className="border-b border-white/20 py-12 md:py-16" data-case-block>
            <div className="container mx-auto max-w-5xl">
                {(eyebrow || title) && <SectionIntro eyebrow={eyebrow} title={title} />}
                <figure className={eyebrow || title ? 'mt-10 text-center md:mt-14' : 'text-center'} data-client-reveal>
                    <Quote className="mx-auto h-7 w-7 text-socies-green" aria-hidden="true" />
                    <blockquote className="mx-auto mt-5 max-w-4xl whitespace-pre-line text-lg font-light leading-relaxed md:text-xl">
                        &ldquo;{visibleQuote}&rdquo;
                    </blockquote>
                    {hasLongQuote && (
                        <button
                            type="button"
                            className="mt-4 text-sm font-semibold text-socies-green underline decoration-transparent underline-offset-4 transition hover:decoration-current focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-focus"
                            aria-expanded={isExpanded}
                            onClick={() => setIsExpanded((expanded) => !expanded)}
                        >
                            {isExpanded ? 'Leer menos' : 'Leer más'}
                        </button>
                    )}
                    <figcaption className="mx-auto mt-8 flex w-fit items-center gap-3 text-left">
                        <span className="flex size-12 shrink-0 items-center justify-center rounded-full border border-white/30 text-sm font-bold" aria-hidden="true">
                            {initials}
                        </span>
                        <span className="text-sm md:text-base">
                            <strong className="block">{testimonial.person}</strong>
                            <span className="mt-1 block text-gray-2">{testimonial.role}</span>
                        </span>
                    </figcaption>
                </figure>
            </div>
        </section>
    )
}
