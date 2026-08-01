import { useEffect, useMemo, useRef, useState } from 'react'
import { createPortal } from 'react-dom'
import { ArrowDownRight, Quote, X } from 'lucide-react'

const TESTIMONIAL_DURATION_MS = 1000
const IMAGE_DEFAULT_DURATION_MS = 1000
const TV_STATIC_MIN_DURATION_MS = 200
const TV_STATIC_MAX_DURATION_MS = 500
const TV_STATIC_SOURCE = '/media/client-story-static.mp4'

function buildChannels(client) {
    return (client.previewItems ?? []).map((item) => ({
            ...item,
            duration: item.durationMs
                ?? (item.type === 'video'
                    ? null
                    : item.type === 'testimonial'
                        ? TESTIMONIAL_DURATION_MS
                        : IMAGE_DEFAULT_DURATION_MS),
        }))
}

function prefersReducedMotion() {
    return typeof window !== 'undefined'
        && window.matchMedia('(prefers-reduced-motion: reduce)').matches
}

function randomInteger(min, max) {
    return Math.floor(Math.random() * (max - min + 1)) + min
}

function useChannelTimeline(channels, isActive, resolvedDurations) {
    const [activeIndex, setActiveIndex] = useState(0)
    const [transition, setTransition] = useState(null)

    useEffect(() => {
        setActiveIndex(0)
        setTransition(null)
    }, [channels])

    useEffect(() => {
        if (!isActive) {
            setActiveIndex(0)
            setTransition(null)
        }
    }, [isActive])

    useEffect(() => {
        const activeChannel = channels[activeIndex]
        const duration = activeChannel?.duration
            ?? resolvedDurations[activeChannel?.id]

        if (!isActive || channels.length < 2 || !duration) {
            return undefined
        }

        let transitionTimer
        const channelTimer = window.setTimeout(() => {
            if (prefersReducedMotion()) {
                setActiveIndex((index) => (index + 1) % channels.length)

                return
            }

            const transitionDuration = randomInteger(
                TV_STATIC_MIN_DURATION_MS,
                TV_STATIC_MAX_DURATION_MS,
            )

            setTransition({
                duration: transitionDuration,
                startRatio: Math.random(),
                key: `${activeChannel.id}-${Date.now()}`,
            })

            transitionTimer = window.setTimeout(() => {
                setActiveIndex((index) => (index + 1) % channels.length)
                setTransition(null)
            }, transitionDuration)
        }, duration)

        return () => {
            window.clearTimeout(channelTimer)
            window.clearTimeout(transitionTimer)
        }
    }, [activeIndex, channels, isActive, resolvedDurations])

    return {
        activeChannel: channels[activeIndex] ?? null,
        transition,
    }
}

function TestimonialChannel({ testimonial }) {
    return (
        <figure className="client-story-card__testimonial">
            <div className="client-story-card__quote-row">
                <Quote
                    aria-hidden="true"
                    className="client-story-card__quote-icon"
                    fill="currentColor"
                />
                <blockquote className="client-story-card__quote">
                    {testimonial.text}
                </blockquote>
            </div>

            {(testimonial.person || testimonial.position) && (
                <figcaption className="client-story-card__attribution">
                    {testimonial.person && (
                        <strong className="client-story-card__person">
                            {testimonial.person}
                        </strong>
                    )}
                    {testimonial.position && (
                        <span className="client-story-card__position">
                            {testimonial.position}
                        </span>
                    )}
                </figcaption>
            )}
        </figure>
    )
}

function VideoChannel({ channel, isActive, onDurationResolved }) {
    const videoRef = useRef(null)

    useEffect(() => {
        const video = videoRef.current

        if (!video) {
            return
        }

        if (isActive) {
            video.currentTime = 0
            video.play().catch(() => {})
        } else {
            video.pause()
        }
    }, [channel.id, isActive])

    return (
        <video
            ref={videoRef}
            className="client-story-card__media"
            src={channel.url}
            autoPlay={isActive}
            muted
            playsInline
            preload="metadata"
            loop={channel.duration !== null}
            onLoadedMetadata={(event) => {
                if (channel.duration === null && Number.isFinite(event.currentTarget.duration)) {
                    onDurationResolved(channel.id, event.currentTarget.duration * 1000)
                }
            }}
        />
    )
}

function ChannelContent({
    channel,
    isActive,
    onDurationResolved,
}) {
    if (!channel) {
        return null
    }

    if (channel.type === 'testimonial') {
        return <TestimonialChannel testimonial={channel.content} />
    }

    if (channel.type === 'video') {
        return (
            <VideoChannel
                channel={channel}
                isActive={isActive}
                onDurationResolved={onDurationResolved}
            />
        )
    }

    return (
        <img
            className="client-story-card__media"
            src={channel.url}
            alt=""
        />
    )
}

function TvStaticTransition({ transition }) {
    const videoRef = useRef(null)

    useEffect(() => {
        const video = videoRef.current

        if (!video || !transition) {
            video?.pause()

            return
        }

        const playSegment = () => {
            const segmentSeconds = transition.duration / 1000
            const availableStart = Math.max(0, video.duration - segmentSeconds)

            video.currentTime = transition.startRatio * availableStart
            video.play().catch(() => {})
        }

        if (video.readyState >= 1) {
            playSegment()
        } else {
            video.addEventListener('loadedmetadata', playSegment, { once: true })
        }

        return () => {
            video.removeEventListener('loadedmetadata', playSegment)
            video.pause()
        }
    }, [transition])

    return (
        <video
            ref={videoRef}
            className="client-story-card__static"
            src={TV_STATIC_SOURCE}
            autoPlay={false}
            muted
            playsInline
            preload="auto"
            aria-hidden="true"
        />
    )
}

export function ClientStoryCard({
    client,
    isActive = true,
    onClose,
    titleId,
    showLink = true,
}) {
    const channels = useMemo(() => buildChannels(client), [client])
    const [resolvedDurations, setResolvedDurations] = useState({})
    const { activeChannel, transition } = useChannelTimeline(
        channels,
        isActive,
        resolvedDurations,
    )
    const titleColor = client.popupTextColor === 'black'
        ? 'var(--color-black)'
        : 'var(--color-white)'

    const resolveDuration = (channelId, duration) => {
        setResolvedDurations((current) => (
            current[channelId] === duration
                ? current
                : { ...current, [channelId]: duration }
        ))
    }

    return (
        <article
            className="client-story-card"
            style={{
                '--client-story-accent': client.color || 'var(--color-black)',
                '--client-story-title-color': titleColor,
            }}
        >
            <header className="client-story-card__header">
                <ArrowDownRight
                    aria-hidden="true"
                    className="client-story-card__mark"
                    strokeWidth={2}
                />
                <h3 id={titleId} className="client-story-card__title">
                    {client.title || client.alt}
                </h3>

                {onClose && (
                    <button
                        type="button"
                        className="client-story-card__close"
                        aria-label={`Cerrar historia de ${client.title || client.alt}`}
                        onClick={onClose}
                    >
                        <X aria-hidden="true" />
                    </button>
                )}
            </header>

            <div
                className={`client-story-card__screen${transition ? ' is-switching' : ''}`}
                data-channel={activeChannel?.id}
            >
                <div className="client-story-card__channel" aria-live="off">
                    <ChannelContent
                        channel={activeChannel}
                        isActive={isActive && !transition}
                        onDurationResolved={resolveDuration}
                    />
                </div>
                <TvStaticTransition transition={transition} />
            </div>
            {showLink && client.url && (
                <a className="client-story-card__link" href={client.url}>
                    Ver cliente <span aria-hidden="true">↗</span>
                </a>
            )}
        </article>
    )
}

export function ClientStoryModal({ client, onClose }) {
    const dialogRef = useRef(null)
    const previousFocusRef = useRef(null)
    const [isMounted, setIsMounted] = useState(false)
    const titleId = `client-story-modal-title-${client.id}`

    useEffect(() => {
        setIsMounted(true)
    }, [])

    useEffect(() => {
        if (!isMounted) {
            return undefined
        }

        const dialog = dialogRef.current
        previousFocusRef.current = document.activeElement
        dialog?.showModal()

        return () => {
            dialog?.close()
            previousFocusRef.current?.focus?.()
        }
    }, [isMounted])

    if (!isMounted) {
        return null
    }

    return createPortal(
        <dialog
            ref={dialogRef}
            className="client-story-dialog"
            aria-labelledby={titleId}
            onCancel={(event) => {
                event.preventDefault()
                onClose()
            }}
            onClick={(event) => {
                if (event.target === event.currentTarget) {
                    onClose()
                }
            }}
        >
            <ClientStoryCard
                client={client}
                isActive
                onClose={onClose}
                titleId={titleId}
            />
        </dialog>,
        document.body,
    )
}
