import { useEffect, useRef, useState } from 'react'
import TextareaAutosize from 'react-textarea-autosize'
import { PacoContactForm } from './PacoContactForm'
import type { PacoAction, PacoPart } from './types'

type Props = {
    part: PacoPart
    conversationId: string
    disabled: boolean
    onSubmit: (action: PacoAction) => Promise<unknown>
}

export function PacoPartRenderer({ part, conversationId, disabled, onSubmit }: Props) {
    if (part.type === 'contact_form') {
        return <PacoContactForm disabled={disabled} onSubmit={onSubmit} />
    }
    if (part.type === 'single_select') {
        return <SingleSelect part={part} disabled={disabled} onSubmit={onSubmit} />
    }
    if (part.type === 'multi_select') {
        return <MultiSelect part={part} disabled={disabled} onSubmit={onSubmit} />
    }
    if (part.type === 'date') {
        return <DateInput part={part} disabled={disabled} onSubmit={onSubmit} />
    }

    return <TextInput part={part} conversationId={conversationId} disabled={disabled} onSubmit={onSubmit} />
}

function TextInput({ part, conversationId, disabled, onSubmit }: Props) {
    const draftKey = `socies-paco-draft:${conversationId}:${part.id}`
    const [value, setValue] = useState(() => sessionStorage.getItem(draftKey) || '')

    return (
        <form className="paco-composer" onSubmit={async (event) => {
            event.preventDefault()
            if (value.trim().length < 3) return
            await onSubmit({ type: 'text_submit', component_id: part.id, value: value.trim() })
            sessionStorage.removeItem(draftKey)
            setValue('')
        }}>
            <label className="sr-only" htmlFor={`paco-input-${part.id}`}>Tu respuesta</label>
            <TextareaAutosize
                id={`paco-input-${part.id}`}
                minRows={2}
                maxRows={6}
                maxLength={part.max_length || 1500}
                value={value}
                placeholder={part.placeholder}
                disabled={disabled}
                onChange={(event) => {
                    setValue(event.target.value)
                    sessionStorage.setItem(draftKey, event.target.value)
                }}
                onKeyDown={(event) => {
                    if (event.key === 'Enter' && !event.shiftKey) {
                        event.preventDefault()
                        event.currentTarget.form?.requestSubmit()
                    }
                }}
            />
            <button type="submit" disabled={disabled || value.trim().length < 3} aria-label="Enviar respuesta">
                <span aria-hidden="true">↗</span>
            </button>
        </form>
    )
}

function SingleSelect({ part, disabled, onSubmit }: Omit<Props, 'conversationId'>) {
    const [selectedValue, setSelectedValue] = useState<string | null>(null)
    const [detail, setDetail] = useState('')
    const detailRef = useRef<HTMLTextAreaElement>(null)
    const selected = part.options?.find((option) => option.value === selectedValue)

    useEffect(() => {
        if (selected?.allow_detail) detailRef.current?.focus()
    }, [selected?.allow_detail, selectedValue])

    return (
        <form className="paco-option-response" onSubmit={(event) => {
            event.preventDefault()
            if (!selected?.allow_detail) return
            void onSubmit({
                type: 'single_select',
                component_id: part.id,
                value: { choice: selected.value, detail: detail.trim() || null },
            })
        }}>
            <div className="paco-options" role="group" aria-label="Opciones de respuesta">
                {part.options?.map((option) => (
                    <button
                        key={option.value}
                        type="button"
                        disabled={disabled}
                        aria-pressed={selectedValue === option.value}
                        onClick={() => {
                            if (!option.allow_detail) {
                                void onSubmit({ type: 'single_select', component_id: part.id, value: option.value })
                                return
                            }
                            setSelectedValue(option.value)
                            setDetail('')
                        }}
                    >
                        {option.label}
                    </button>
                ))}
            </div>

            {selected?.allow_detail && (
                <div className="paco-option-detail">
                    <label htmlFor={`paco-detail-${part.id}`}>
                        {selected.detail_label || 'Si querés, contanos un poco más'}
                    </label>
                    <TextareaAutosize
                        ref={detailRef}
                        id={`paco-detail-${part.id}`}
                        minRows={2}
                        maxRows={5}
                        maxLength={selected.detail_max_length || 600}
                        value={detail}
                        placeholder={selected.detail_placeholder || 'Escribí una aclaración breve'}
                        disabled={disabled}
                        required={selected.detail_required}
                        onChange={(event) => setDetail(event.target.value)}
                    />
                    <button
                        className="paco-submit"
                        type="submit"
                        disabled={disabled || Boolean(selected.detail_required && detail.trim().length < 3)}
                    >
                        Continuar
                    </button>
                </div>
            )}
        </form>
    )
}

function MultiSelect({ part, disabled, onSubmit }: Omit<Props, 'conversationId'>) {
    const [selected, setSelected] = useState<string[]>([])

    return (
        <form className="paco-multi" onSubmit={(event) => {
            event.preventDefault()
            void onSubmit({ type: 'multi_select', component_id: part.id, value: selected })
        }}>
            {part.options?.map((option) => (
                <label key={option.value}>
                    <input
                        type="checkbox"
                        value={option.value}
                        checked={selected.includes(option.value)}
                        disabled={disabled}
                        onChange={(event) => setSelected((current) => event.target.checked
                            ? [...current, option.value]
                            : current.filter((value) => value !== option.value))}
                    />
                    <span>{option.label}</span>
                </label>
            ))}
            <button className="paco-submit" type="submit" disabled={disabled || (part.required && selected.length === 0)}>
                Continuar
            </button>
        </form>
    )
}

function DateInput({ part, disabled, onSubmit }: Omit<Props, 'conversationId'>) {
    const [value, setValue] = useState('')

    return (
        <form className="paco-date" onSubmit={(event) => {
            event.preventDefault()
            void onSubmit({ type: 'date_submit', component_id: part.id, value })
        }}>
            <label htmlFor={`paco-date-${part.id}`}>Fecha aproximada</label>
            <input
                id={`paco-date-${part.id}`}
                type="date"
                value={value}
                disabled={disabled}
                onChange={(event) => setValue(event.target.value)}
            />
            <button className="paco-submit" type="submit" disabled={disabled || (!value && part.required)}>Continuar</button>
        </form>
    )
}
