import { useEffect, useMemo, useRef, useState } from 'react'
import { PacoMessageThread } from './PacoMessageThread'
import { PacoPartRenderer } from './PacoPartRenderer'
import { PacoPrefillReview } from './PacoPrefillReview'
import type { PacoConversationState, PacoEntry } from './types'
import { PacoApiError } from './pacoApi'
import { usePacoConversation } from './usePacoConversation'

type Props = {
    mode?: 'inline' | 'page'
    entry?: Partial<PacoEntry>
    readOnly?: boolean
    readOnlyState?: PacoConversationState | null
}

export default function SociesConversation({ mode = 'inline', entry = {}, readOnly = false, readOnlyState = null }: Props) {
    const sectionRef = useRef<HTMLElement>(null)
    const [visible, setVisible] = useState(mode === 'page' || readOnly)
    const resolvedEntry = useMemo<PacoEntry>(() => ({
        campaign: entry.campaign || (mode === 'page' ? 'direct_default' : 'home_default'),
        prefillToken: entry.prefillToken || null,
        utmSource: entry.utmSource || null,
        utmMedium: entry.utmMedium || null,
        utmCampaign: entry.utmCampaign || null,
        pageContext: entry.pageContext || null,
    }), [entry.campaign, entry.prefillToken, entry.utmSource, entry.utmMedium, entry.utmCampaign, entry.pageContext, mode])
    const conversation = usePacoConversation(resolvedEntry, visible && !readOnly, readOnlyState)

    useEffect(() => {
        if (mode === 'page' || readOnly || !sectionRef.current) return
        const observer = new IntersectionObserver(([item]) => {
            if (item.isIntersecting) {
                setVisible(true)
                observer.disconnect()
            }
        }, { rootMargin: '320px 0px' })
        observer.observe(sectionRef.current)
        return () => observer.disconnect()
    }, [mode, readOnly])

    useEffect(() => {
        if (!conversation.state || !resolvedEntry.prefillToken) return
        const url = new URL(window.location.href)
        url.searchParams.delete('prefill_token')
        window.history.replaceState({}, '', `${url.pathname}${url.search}${url.hash}`)
    }, [conversation.state?.conversation_id, resolvedEntry.prefillToken])

    const latestAssistant = conversation.state?.turns
        .filter((turn) => turn.actor === 'assistant')
        .at(-1)
    const canAnswer = conversation.state?.status === 'active' && latestAssistant
    const Title = mode === 'page' ? 'h1' : 'h2'
    const rateLimited = conversation.error instanceof PacoApiError && conversation.error.status === 429
    const showDevelopmentControls = Boolean((import.meta as ImportMeta & { env?: { DEV?: boolean } }).env?.DEV)

    return (
        <section
            id="hablemos"
            ref={sectionRef}
            className={`paco-section paco-section--${mode}`}
            aria-labelledby={`paco-title-${mode}`}
        >
            <div className="paco-shell">
                <header className="paco-intro">
                    <span>HABLEMOS</span>
                    <Title id={`paco-title-${mode}`}>{mode === 'page' ? 'Contanos qué necesitas resolver' : '¿Qué necesitas resolver?'}</Title>
                    <p>Una conversación breve para entender el problema y darle contexto a nuestro equipo.</p>
                </header>

                <div className="paco-panel" aria-busy={conversation.isStarting || conversation.isSending} aria-readonly={readOnly || undefined}>
                    <div className="paco-panel__topbar">
                        <div>
                            <span className="paco-panel__status" aria-hidden="true" />
                            <strong>Socies</strong>
                        </div>
                        <div className="paco-panel__meta">
                            <span>{conversation.state?.status === 'closed' ? 'Consulta recibida' : 'En línea'}</span>
                            {showDevelopmentControls && !readOnly && (
                                <button
                                    className="paco-dev-reset"
                                    type="button"
                                    onClick={conversation.startAgain}
                                    disabled={conversation.isStarting || conversation.isSending}
                                    aria-label="Reiniciar conversación de desarrollo"
                                    title="Solo visible en desarrollo"
                                >
                                    Reiniciar charla
                                </button>
                            )}
                        </div>
                    </div>

                    {conversation.isStarting && <PacoLoading />}

                    {conversation.error && !conversation.state && (
                        <div className="paco-state paco-state--error" role="alert">
                            <strong>{rateLimited ? 'Gracias por escribirnos.' : 'No pudimos iniciar la conversación.'}</strong>
                            <p>{conversation.error.message}</p>
                            <button type="button" onClick={rateLimited ? conversation.startAgain : conversation.retryStart}>
                                {rateLimited ? 'Iniciar otra consulta' : 'Intentar nuevamente'}
                            </button>
                        </div>
                    )}

                    {conversation.state && (
                        <>
                            <PacoMessageThread turns={conversation.state.turns} isSending={conversation.isSending} />

                            {!readOnly && <div className="paco-controls">
                                {conversation.error && (
                                    <p className="paco-inline-error" role="alert">{conversation.error.message}</p>
                                )}

                                {conversation.prefill?.requires_confirmation && conversation.state.turns.length === 1 ? (
                                    <PacoPrefillReview
                                        prefill={conversation.prefill}
                                        disabled={conversation.isSending}
                                        onSubmit={conversation.send}
                                    />
                                ) : canAnswer ? latestAssistant.parts
                                    .filter((part) => part.type !== 'content_carousel')
                                    .map((part) => (
                                    <PacoPartRenderer
                                        key={part.id}
                                        part={part}
                                        conversationId={conversation.state!.conversation_id}
                                        disabled={conversation.isSending}
                                        onSubmit={conversation.send}
                                    />
                                    )) : null}

                                {conversation.state.status === 'closed' && (
                                    <button className="paco-new-conversation" type="button" onClick={conversation.startAgain}>
                                        Iniciar otra consulta
                                    </button>
                                )}
                            </div>}
                        </>
                    )}
                </div>
            </div>
        </section>
    )
}

function PacoLoading() {
    return (
        <div className="paco-loading" role="status" aria-label="Iniciando conversación">
            <span /><span /><span />
        </div>
    )
}
