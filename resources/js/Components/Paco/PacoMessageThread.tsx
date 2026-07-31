import { useEffect, useRef } from 'react'
import type { PacoTurn } from './types'
import { PacoEvidence } from './PacoEvidence'

export function PacoMessageThread({ turns, isSending }: { turns: PacoTurn[]; isSending: boolean }) {
    const viewportRef = useRef<HTMLDivElement>(null)
    const previousCount = useRef(turns.length)

    useEffect(() => {
        const viewport = viewportRef.current
        if (!viewport || turns.length === previousCount.current) return

        const nearBottom = viewport.scrollHeight - viewport.scrollTop - viewport.clientHeight < 180
        const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches
        if (nearBottom) viewport.scrollTo({
            top: viewport.scrollHeight,
            behavior: reducedMotion ? 'auto' : 'smooth',
        })
        previousCount.current = turns.length
    }, [turns.length])

    return (
        <div className="paco-thread" ref={viewportRef} role="log" aria-live="polite" aria-relevant="additions">
            {turns.map((turn) => {
                const evidence = turn.parts.find((part) => part.type === 'content_carousel')

                return (
                    <article
                        key={turn.id}
                        className={`paco-message paco-message--${turn.actor === 'assistant' ? 'socies' : 'visitor'}${evidence ? ' paco-message--with-evidence' : ''}`}
                    >
                        {turn.actor === 'assistant' && <span className="paco-message__author">SOCIES</span>}
                        <MessageContent turn={turn} evidenceItems={evidence?.items} />
                    </article>
                )
            })}
            {isSending && (
                <div className="paco-typing" role="status">
                    <span>Socies está respondiendo</span>
                    <i aria-hidden="true" /><i aria-hidden="true" /><i aria-hidden="true" />
                </div>
            )}
        </div>
    )
}

function MessageContent({ turn, evidenceItems }: { turn: PacoTurn; evidenceItems?: NonNullable<PacoTurn['parts'][number]['items']> }) {
    const question = turn.actor === 'assistant' && typeof turn.meta?.question_text === 'string'
        ? turn.meta.question_text
        : null
    const hasStructuredQuestion = question !== null && turn.message.trimEnd().endsWith(question)
    const body = hasStructuredQuestion
        ? turn.message.trimEnd().slice(0, -question.length).trim()
        : turn.message.trim()
    const paragraphs = body.split(/\n{2,}/).map((paragraph) => paragraph.trim()).filter(Boolean)

    return (
        <div className="paco-message__content">
            {paragraphs.map((paragraph, index) => <p key={`${turn.id}-paragraph-${index}`}>{paragraph}</p>)}
            {evidenceItems && <PacoEvidence items={evidenceItems} />}
            {hasStructuredQuestion && (
                <p className="paco-message__question">
                    <strong>{question}</strong>
                </p>
            )}
        </div>
    )
}
