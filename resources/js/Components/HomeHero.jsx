import { useLayoutEffect, useRef } from 'react'
import { gsap } from 'gsap'
import { ScrollTrigger } from 'gsap/ScrollTrigger'
import AnimatedHeroTitle from '@/Components/AnimatedHeroTitle'
import ScrollCue from '@/Components/ScrollCue'
import SociesLogo from '@/Components/SociesLogo'

gsap.registerPlugin(ScrollTrigger)

const SCROLL_TRIGGER_ID = 'socies-home-logo-handoff'
let primaryIntroPlayedInRuntime = false

export const HOME_HERO = {
    title: 'Convertimos problemas complejos en sistemas que impulsan negocio',
    highlightWord: 'sistemas',
    durationScale: 1,
    logoSettleDuration: 0.72,
    logoBottomOffset: 30,
    mobileLogoBottomOffset: 30,
    followSpacing: 30,
    scrollCueDelay: 4,
    scrollCueDismissDistance: 300,
}

function groupCharactersByRenderedLine(titleElement) {
    const lines = new Map()
    const words = titleElement.querySelectorAll('[data-title-word]')

    words.forEach((word) => {
        const lineTop = Math.round(word.offsetTop)
        const line = lines.get(lineTop) ?? []
        line.push(...word.querySelectorAll('[data-title-character]'))
        lines.set(lineTop, line)
    })

    return [...lines.entries()]
        .sort(([firstTop], [secondTop]) => firstTop - secondTop)
        .map(([, characters]) => characters)
}

function addTitleReveal(timeline, titleElement, position, { onArrowPulse, onDotPulse } = {}) {
    const lines = groupCharactersByRenderedLine(titleElement)
    const marks = [...titleElement.querySelectorAll('[data-title-mark]')]
    const [arrow, dot] = marks

    timeline.addLabel('title', position)

    timeline.to(arrow, {
        autoAlpha: 1,
        scale: 1,
        duration: 0.16,
        ease: 'back.out(1.5)',
    }, 'title')

    timeline.call(onArrowPulse)
    timeline.addLabel('text')

    lines.forEach((characters, lineIndex) => {
        timeline.to(characters, {
            yPercent: 0,
            duration: 0.48,
            ease: 'power4.out',
            stagger: 0.018,
        }, lineIndex === 0 ? 'text' : '>')
    })

    timeline.to(dot, {
        autoAlpha: 1,
        scale: 1.08,
        duration: 0.16,
        ease: 'back.out(1.7)',
    })

    timeline.to(dot, {
        scale: 1,
        duration: 0.1,
        ease: 'power2.out',
    })

    timeline.call(onDotPulse)
}

export default function HomeHero() {
    const rootRef = useRef(null)
    const lineRef = useRef(null)
    const logoMotionRef = useRef(null)
    const logoSvgRef = useRef(null)
    const titleRef = useRef(null)
    const scrollCueRef = useRef(null)

    useLayoutEffect(() => {
        const root = rootRef.current
        const line = lineRef.current
        const logoMotion = logoMotionRef.current
        const logoSvg = logoSvgRef.current
        const title = titleRef.current
        const scrollCue = scrollCueRef.current
        const message = root.querySelector('.home-hero__message')
        const headerLogo = document.querySelector('[data-header-logo-target]')
        const headerLine = document.querySelector('[data-header-line]')
        const clientLogos = document.querySelector('[data-client-logos]')
        const handoffRunway = document.querySelector('[data-home-handoff-runway]')

        if (!root || !line || !logoMotion || !logoSvg || !title || !scrollCue || !message || !headerLogo || !headerLine) {
            return undefined
        }

        const track = logoSvg.querySelector('[data-logo-track]')
        const letters = [...logoSvg.querySelectorAll('[data-logo-letter]')]
        const circles = logoSvg.querySelectorAll('[data-logo-circle]')
        const glyphs = logoSvg.querySelectorAll('[data-logo-glyph]')
        const characters = title.querySelectorAll('[data-title-character]')
        const marks = title.querySelectorAll('[data-title-mark]')
        const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches
        const durationScale = Math.max(0.1, HOME_HERO.durationScale)
        const duration = (value) => value * durationScale
        let handoffActive = null
        let introCompleted = false
        let scrollTimeline
        let scrollCueTimer
        let scrollCueBounce
        let scrollCueTransition
        let scrollCueVisible = false
        let scrollCueDismissed = false
        let titleArrowPulse
        let titleDotPulse
        let rebuildScrollHandoff
        const initialScrollY = window.scrollY
        let userScrolledBeforeCue = initialScrollY > 1
        let scrollCueStartY = initialScrollY

        const dismissScrollCue = () => {
            if (scrollCueDismissed) return

            scrollCueDismissed = true
            window.clearTimeout(scrollCueTimer)
            scrollCueBounce?.kill()

            if (scrollCueVisible) {
                scrollCueTransition?.kill()
                scrollCueTransition = gsap.to(scrollCue, {
                    autoAlpha: 0,
                    y: 0,
                    duration: reduceMotion ? 0 : 0.22,
                    ease: 'power2.out',
                    overwrite: true,
                })
            }
        }

        const handleScrollCueScroll = () => {
            if (!scrollCueVisible) {
                if (Math.abs(window.scrollY - initialScrollY) > 1) {
                    userScrolledBeforeCue = true
                    window.clearTimeout(scrollCueTimer)
                }

                return
            }

            if (Math.abs(window.scrollY - scrollCueStartY) >= HOME_HERO.scrollCueDismissDistance) {
                dismissScrollCue()
            }
        }

        const scheduleScrollCue = () => {
            if (
                scrollCueDismissed
                || userScrolledBeforeCue
                || Math.abs(window.scrollY - initialScrollY) > 1
            ) {
                return
            }

            window.clearTimeout(scrollCueTimer)
            scrollCueTimer = window.setTimeout(() => {
                if (userScrolledBeforeCue || scrollCueDismissed) return

                scrollCueVisible = true
                scrollCueStartY = window.scrollY
                scrollCueTransition = gsap.to(scrollCue, {
                    autoAlpha: 1,
                    duration: reduceMotion ? 0 : 0.3,
                    ease: 'power2.out',
                    overwrite: true,
                })

                if (!reduceMotion) {
                    scrollCueBounce = gsap.to(scrollCue, {
                        y: 8,
                        duration: 0.65,
                        ease: 'sine.inOut',
                        repeat: -1,
                        yoyo: true,
                    })
                }
            }, HOME_HERO.scrollCueDelay * 1000)
        }

        window.addEventListener('scroll', handleScrollCueScroll, { passive: true })

        const context = gsap.context(() => {
            const startTitleArrowPulse = () => {
                titleArrowPulse?.kill()
                titleArrowPulse = gsap.to(title.querySelector('.home-hero__arrow'), {
                    xPercent: -8,
                    yPercent: 8,
                    duration: 0.22,
                    ease: 'sine.inOut',
                    repeat: -1,
                    yoyo: true,
                })
            }

            const startTitleDotPulse = () => {
                titleDotPulse?.kill()
                titleDotPulse = gsap.fromTo(
                    title.querySelector('.home-hero__dot'),
                    { scale: 0.82 },
                    {
                        scale: 1.08,
                        duration: 0.32,
                        ease: 'sine.inOut',
                        repeat: -1,
                        yoyo: true,
                    },
                )
            }

            const getBottomOffset = () => (
                window.matchMedia('(max-width: 767px)').matches
                    ? HOME_HERO.mobileLogoBottomOffset
                    : HOME_HERO.logoBottomOffset
            )

            const getLogoHeight = () => (
                logoMotion.offsetHeight || logoMotion.getBoundingClientRect().height
            )

            const getFinalLogoY = () => (
                window.innerHeight / 2
                - getLogoHeight() / 2
                - getBottomOffset()
            )

            const positionHeaderLine = () => {
                const headerLogoRect = headerLogo.getBoundingClientRect()
                const headerLogoCenterY = headerLogoRect.top + headerLogoRect.height / 2
                headerLine.style.top = `${headerLogoCenterY - headerLine.offsetHeight / 2}px`
            }

            const getFinalLineY = () => (
                getFinalLogoY()
                + window.innerHeight / 2
                - line.offsetHeight / 2
            )

            const setHandoff = (active) => {
                if (handoffActive === active) return

                handoffActive = active
                root.classList.toggle('is-logo-transfer', !active)
                root.classList.toggle('is-handoff-complete', active)
                logoMotion.style.visibility = active ? 'hidden' : 'visible'
                line.style.visibility = active ? 'hidden' : 'visible'
                headerLogo.style.visibility = active ? 'visible' : 'hidden'
                headerLine.style.visibility = active ? 'visible' : 'hidden'
            }

            const setFinalHeroState = () => {
                gsap.set(track, { x: 0 })
                gsap.set(letters, { x: 0, scale: 1, autoAlpha: 1 })
                gsap.set(circles, { fill: 'var(--color-white)' })
                gsap.set(glyphs, { fill: 'var(--color-black)' })
                gsap.set(logoMotion, { x: 0, y: getFinalLogoY(), scale: 1 })
                gsap.set(message, { y: 0 })
                gsap.set(line, {
                    y: getFinalLineY(),
                    scaleX: 0,
                    autoAlpha: 1,
                    transformOrigin: '50% 50%',
                })
                setHandoff(false)
            }

            const setupScrollHandoff = () => {
                scrollTimeline?.kill()
                ScrollTrigger.getById(SCROLL_TRIGGER_ID)?.kill()
                setHandoff(false)
                positionHeaderLine()
                gsap.set(line, {
                    scaleX: 0,
                    autoAlpha: 1,
                    transformOrigin: '50% 50%',
                })

                const logoRect = logoMotion.getBoundingClientRect()
                const targetRect = headerLogo.getBoundingClientRect()
                const lineRect = line.getBoundingClientRect()
                const headerLineRect = headerLine.getBoundingClientRect()
                const messageRect = message.getBoundingClientRect()
                const startX = Number(gsap.getProperty(logoMotion, 'x')) || 0
                const startY = Number(gsap.getProperty(logoMotion, 'y')) || 0
                const lineStartY = Number(gsap.getProperty(line, 'y')) || 0
                const deltaX = targetRect.left + targetRect.width / 2
                    - (logoRect.left + logoRect.width / 2)
                const deltaY = targetRect.top + targetRect.height / 2
                    - (logoRect.top + logoRect.height / 2)
                const targetScale = targetRect.width / logoRect.width
                const lineDeltaY = headerLineRect.top - lineRect.top
                const followSpacing = Number.parseFloat(
                    clientLogos ? getComputedStyle(clientLogos).getPropertyValue('--home-hero-follow-spacing') : '',
                ) || HOME_HERO.followSpacing
                const desiredClientTop = headerLineRect.bottom + followSpacing
                const clientTop = clientLogos
                    ? clientLogos.getBoundingClientRect().top + window.scrollY
                    : Number.NaN
                const contentScrollDistance = Number.isFinite(clientTop)
                    ? Math.max(0, clientTop - desiredClientTop)
                    : 0
                const preferredScrollDistance = Math.max(
                    Math.abs(deltaY),
                    contentScrollDistance,
                    window.innerHeight * 0.55,
                )
                const currentRunwayHeight = handoffRunway?.getBoundingClientRect().height || 0
                const availableScrollDistance = Math.max(
                    document.documentElement.scrollHeight - currentRunwayHeight - window.innerHeight,
                    1,
                )
                const runwayHeight = Math.max(0, preferredScrollDistance - availableScrollDistance)
                handoffRunway?.style.setProperty('--home-handoff-runway-height', `${Math.ceil(runwayHeight)}px`)
                const scrollDistance = preferredScrollDistance
                const messageExitY = -messageRect.bottom - 24

                scrollTimeline = gsap.timeline({
                    defaults: { ease: 'none' },
                    scrollTrigger: {
                        id: SCROLL_TRIGGER_ID,
                        trigger: root,
                        start: 'top top',
                        end: `+=${scrollDistance}`,
                        pin: true,
                        pinSpacing: false,
                        scrub: 0.2,
                        invalidateOnRefresh: true,
                        onUpdate: ({ progress }) => setHandoff(progress >= 0.995),
                        onRefresh: ({ progress }) => setHandoff(progress >= 0.995),
                    },
                })

                scrollTimeline.to(logoMotion, {
                    x: startX + deltaX,
                    y: startY + deltaY,
                    scale: targetScale,
                }, 0)

                scrollTimeline.to(line, {
                    y: lineStartY + lineDeltaY,
                    scaleX: 1,
                }, 0)

                scrollTimeline.to(message, {
                    y: messageExitY,
                }, 0)

                ScrollTrigger.refresh()
            }

            rebuildScrollHandoff = () => {
                setFinalHeroState()
                setupScrollHandoff()
            }

            gsap.set(headerLogo, { visibility: 'hidden' })
            gsap.set(headerLine, { clearProps: 'visibility' })
            headerLine.style.visibility = 'hidden'
            positionHeaderLine()
            gsap.set(characters, { yPercent: 115 })
            gsap.set(marks, { autoAlpha: 0, scale: 0.65 })
            gsap.set(scrollCue, { autoAlpha: 0, y: 0 })

            if (reduceMotion) {
                primaryIntroPlayedInRuntime = true
                introCompleted = true
                root.classList.remove('is-intro')
                document.documentElement.classList.remove('hero-scroll-lock')
                setFinalHeroState()
                gsap.set(characters, { yPercent: 0 })
                gsap.set(marks, { autoAlpha: 1, scale: 1 })
                gsap.set(line, { scaleX: 1 })
                scheduleScrollCue()
                return
            }

            if (primaryIntroPlayedInRuntime) {
                introCompleted = true
                root.classList.remove('is-intro')
                setFinalHeroState()

                const titleTimeline = gsap.timeline()
                addTitleReveal(titleTimeline, title, 0, {
                    onArrowPulse: startTitleArrowPulse,
                    onDotPulse: startTitleDotPulse,
                })
                titleTimeline.eventCallback('onComplete', () => {
                    scheduleScrollCue()
                })
                requestAnimationFrame(setupScrollHandoff)
                return
            }

            primaryIntroPlayedInRuntime = true
            document.documentElement.classList.add('hero-scroll-lock')

            const trackOffset = logoSvg.viewBox.baseVal.width / 2 - 50.5
            const hiddenLetters = letters.slice(1)

            gsap.set(track, { x: trackOffset })
            gsap.set(letters[0], { autoAlpha: 1, scale: 1, x: 0 })
            hiddenLetters.forEach((letter, index) => {
                const letterIndex = index + 1
                gsap.set(letter, {
                    x: -100 * letterIndex,
                    scale: 0.18,
                    autoAlpha: 0,
                    svgOrigin: `${50 + 100 * letterIndex} 50`,
                })
            })
            gsap.set(line, { scaleX: 0, autoAlpha: 0, y: 0 })
            gsap.set(logoMotion, {
                x: 0,
                y: () => -(window.innerHeight / 2 + logoMotion.getBoundingClientRect().height),
                scale: 1,
            })

            const intro = gsap.timeline({
                onComplete: () => {
                    introCompleted = true
                    document.documentElement.classList.remove('hero-scroll-lock')
                    root.classList.remove('is-intro')
                    requestAnimationFrame(setupScrollHandoff)
                    scheduleScrollCue()
                },
            })

            intro.addLabel('drop', 0)
                .to(logoMotion, {
                    y: 0,
                    duration: duration(0.82),
                    ease: 'power2.in',
                }, 'drop')
                .to(logoMotion, {
                    y: -30,
                    duration: duration(0.22),
                    ease: 'power2.out',
                })
                .to(logoMotion, {
                    y: 0,
                    duration: duration(0.18),
                    ease: 'power2.in',
                })
                .to(logoMotion, {
                    y: -11,
                    duration: duration(0.14),
                    ease: 'power2.out',
                })
                .to(logoMotion, {
                    y: 0,
                    duration: duration(0.12),
                    ease: 'power2.in',
                })
                .addLabel('assemble')
                .to(track, {
                    x: 0,
                    duration: duration(0.46),
                    ease: 'power3.inOut',
                }, 'assemble')
                .to(hiddenLetters, {
                    x: 0,
                    scale: 1,
                    autoAlpha: 1,
                    duration: duration(0.34),
                    ease: 'back.out(1.3)',
                    stagger: duration(0.045),
                }, 'assemble')
                .addLabel('invert')
                .to(circles, {
                    fill: 'var(--color-white)',
                    duration: duration(0.3),
                    ease: 'power2.inOut',
                }, 'invert')
                .to(glyphs, {
                    fill: 'var(--color-black)',
                    duration: duration(0.3),
                    ease: 'power2.inOut',
                }, 'invert')
                .to(logoMotion, {
                    y: getFinalLogoY,
                    duration: duration(HOME_HERO.logoSettleDuration),
                    ease: 'power2.inOut',
                }, 'invert')
                .to(line, {
                    y: getFinalLineY,
                    scaleX: 0,
                    autoAlpha: 1,
                    duration: duration(HOME_HERO.logoSettleDuration),
                    ease: 'power2.inOut',
                }, 'invert')

            addTitleReveal(intro, title, '>', {
                onArrowPulse: startTitleArrowPulse,
                onDotPulse: startTitleDotPulse,
            })
        }, root)

        const handleFontsReady = () => ScrollTrigger.refresh()
        const handleResize = () => {
            if (introCompleted) {
                rebuildScrollHandoff?.()
                return
            }

            positionHeaderLine()
            ScrollTrigger.refresh()
        }
        window.addEventListener('resize', handleResize)
        document.fonts?.ready.then(handleFontsReady)

        return () => {
            window.removeEventListener('scroll', handleScrollCueScroll)
            window.removeEventListener('resize', handleResize)
            window.clearTimeout(scrollCueTimer)
            scrollCueBounce?.kill()
            scrollCueTransition?.kill()
            titleArrowPulse?.kill()
            titleDotPulse?.kill()
            scrollTimeline?.kill()
            ScrollTrigger.getById(SCROLL_TRIGGER_ID)?.kill()
            handoffRunway?.style.removeProperty('--home-handoff-runway-height')
            context.revert()
            root.classList.remove('is-logo-transfer')
            root.classList.remove('is-handoff-complete')
            document.documentElement.classList.remove('hero-scroll-lock')
            headerLogo.style.visibility = ''
            headerLine.style.visibility = ''
        }
    }, [])

    return (
        <section
            ref={rootRef}
            className="home-hero is-intro"
            aria-labelledby="home-hero-title"
        >
            <div className="home-hero__line-anchor" aria-hidden="true">
                <div ref={lineRef} className="home-hero__line" />
            </div>

            <div className="home-hero__logo-anchor" aria-hidden="true">
                <div ref={logoMotionRef} className="home-hero__logo-motion">
                    <SociesLogo ref={logoSvgRef} variant="brand" className="home-hero__logo" />
                </div>
            </div>

            <div className="home-hero__content">
                <div className="home-hero__message">
                    <AnimatedHeroTitle
                        ref={titleRef}
                        title={HOME_HERO.title}
                        highlightWord={HOME_HERO.highlightWord}
                    />
                    <div className="home-hero__scroll-cue-anchor">
                        <ScrollCue ref={scrollCueRef} />
                    </div>
                </div>
            </div>
        </section>
    )
}
