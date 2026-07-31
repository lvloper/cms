import { Quote } from 'lucide-react'
import SectionIntro from '@/Components/ClientCase/SectionIntro'

export default function ClientTestimonial({ eyebrow, title, testimonials = [] }) {
    return (
        <section className="border-b border-white/20 py-12 md:py-16" data-case-block>
            <div className="container mx-auto">
                <SectionIntro eyebrow={eyebrow} title={title} />
                <div className="mt-10 grid gap-10 md:mt-14 md:grid-cols-2 md:gap-8">
                    {testimonials.map((testimonial, index) => (
                        <figure key={`${testimonial.person}-${index}`} className="border-t border-white/30 pt-6" data-client-reveal>
                            <div>
                                <Quote className="h-7 w-7 text-socies-green" aria-hidden="true" />
                                <blockquote className="mt-5 whitespace-pre-line text-xl font-bold leading-snug md:text-2xl">{testimonial.quote}</blockquote>
                                <figcaption className="mt-6 border-t border-white/20 pt-4 text-sm">
                                    <strong className="block">{testimonial.person}</strong>
                                    <span className="mt-1 block text-gray-2">{testimonial.role}</span>
                                </figcaption>
                            </div>
                        </figure>
                    ))}
                </div>
            </div>
        </section>
    )
}
