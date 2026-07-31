import gsap from 'gsap'
import ScrollTrigger from 'gsap/ScrollTrigger'

gsap.registerPlugin(ScrollTrigger)

function metricParts(value) {
    const raw = String(value ?? '')
    const match = raw.match(/^([^0-9]*)([0-9][0-9.,]*)(.*)$/)

    if (!match) return null

    const [, prefix, numeric, suffix] = match
    const grouped = numeric.includes('.') || numeric.includes(',')
    const target = Number(numeric.replace(/[.,]/g, ''))

    if (!Number.isFinite(target)) return null

    return { prefix, suffix, target, grouped }
}

function animateMetric(element, section) {
    const parts = metricParts(element.dataset.metricValue)

    if (!parts) return null

    const counter = { value: 0 }
    const formatter = new Intl.NumberFormat('es-AR', {
        useGrouping: parts.grouped,
        maximumFractionDigits: 0,
    })

    element.textContent = `${parts.prefix}0${parts.suffix}`

    return gsap.to(counter, {
        value: parts.target,
        duration: 1.4,
        ease: 'power2.out',
        snap: { value: 1 },
        onUpdate: () => {
            element.textContent = `${parts.prefix}${formatter.format(counter.value)}${parts.suffix}`
        },
        scrollTrigger: {
            trigger: section,
            start: 'top 78%',
            once: true,
        },
    })
}

export function initClientCaseAnimations(root) {
    if (!root) return () => {}

    const media = gsap.matchMedia()
    const refreshFrame = requestAnimationFrame(() => ScrollTrigger.refresh())

    media.add(
        {
            isDesktop: '(min-width: 768px)',
            reduceMotion: '(prefers-reduced-motion: reduce)',
        },
        (context) => {
            const { isDesktop, reduceMotion } = context.conditions
            const select = gsap.utils.selector(root)

            if (reduceMotion) {
                gsap.set(select('[data-client-hero-item], [data-client-hero-media], [data-client-reveal], [data-process-node]'), {
                    clearProps: 'all',
                    opacity: 1,
                })

                return
            }

            const heroTimeline = gsap.timeline({
                defaults: { duration: 0.75, ease: 'power3.out' },
            })

            heroTimeline
                .from(select('[data-client-hero-item]'), {
                    opacity: 0,
                    y: 28,
                    stagger: 0.1,
                })
                .from(select('[data-client-hero-media]'), {
                    opacity: 0,
                    x: isDesktop ? 48 : 0,
                    y: isDesktop ? 0 : 24,
                    duration: 0.95,
                }, '<0.15')

            select('[data-case-block]').forEach((section) => {
                const reveals = section.querySelectorAll('[data-client-reveal], [data-process-node]')

                if (reveals.length > 0) {
                    gsap.from(reveals, {
                        opacity: 0,
                        y: 28,
                        duration: 0.7,
                        ease: 'power2.out',
                        stagger: 0.08,
                        scrollTrigger: {
                            trigger: section,
                            start: 'top 82%',
                            once: true,
                        },
                    })
                }

                section.querySelectorAll('[data-metric-counter]').forEach((element) => {
                    animateMetric(element, section)
                })

                if (isDesktop && section.matches('[data-client-projects]')) {
                    const pin = section.querySelector('[data-client-projects-pin]')
                    const viewport = section.querySelector('.client-projects__viewport')
                    const track = section.querySelector('[data-client-projects-track]')

                    if (pin && viewport && track) {
                        const distance = () => Math.max(0, track.scrollWidth - viewport.clientWidth)

                        if (distance() > 1) {
                            gsap.to(track, {
                                x: () => -distance(),
                                ease: 'none',
                                scrollTrigger: {
                                    trigger: section,
                                    start: 'top top',
                                    end: () => `+=${distance()}`,
                                    pin: section,
                                    scrub: 0.8,
                                    invalidateOnRefresh: true,
                                },
                            })
                        }
                    }
                }

                if (isDesktop) {
                    section.querySelectorAll('[data-client-media] img, [data-client-media] video').forEach((element) => {
                        gsap.fromTo(element, {
                            yPercent: -2,
                            scale: 1.04,
                        }, {
                            yPercent: 2,
                            scale: 1.04,
                            ease: 'none',
                            scrollTrigger: {
                                trigger: element,
                                start: 'top bottom',
                                end: 'bottom top',
                                scrub: 0.6,
                            },
                        })
                    })
                }
            })

            if (isDesktop) {
                select('[data-client-hero] [data-client-media] img, [data-client-hero] [data-client-media] video').forEach((element) => {
                    gsap.fromTo(element, {
                        yPercent: -2,
                        scale: 1.04,
                    }, {
                        yPercent: 2,
                        scale: 1.04,
                        ease: 'none',
                        scrollTrigger: {
                            trigger: element,
                            start: 'top bottom',
                            end: 'bottom top',
                            scrub: 0.6,
                        },
                    })
                })
            }

            return () => heroTimeline.kill()
        },
        root,
    )

    return () => {
        cancelAnimationFrame(refreshFrame)
        media.revert()
    }
}
