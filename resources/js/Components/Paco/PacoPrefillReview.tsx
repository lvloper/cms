import { zodResolver } from '@hookform/resolvers/zod'
import { parsePhoneNumberFromString } from 'libphonenumber-js'
import { useForm } from 'react-hook-form'
import { z } from 'zod'
import type { PacoAction, PacoPrefill } from './types'

const prefillSchema = z.object({
    initial_query: z.string().trim().min(3, 'Contanos un poco más.').max(1500),
    name: z.string().trim().min(2, 'Ingresá tu nombre.'),
    contact_channel: z.enum(['email', 'whatsapp']),
    contact_value: z.string().trim().min(5, 'Revisá el dato de contacto.'),
}).superRefine((value, context) => {
    if (value.contact_channel === 'email' && !z.email().safeParse(value.contact_value).success) {
        context.addIssue({ code: 'custom', path: ['contact_value'], message: 'Ingresá un email válido.' })
    }
    if (value.contact_channel === 'whatsapp' && !parsePhoneNumberFromString(value.contact_value, 'AR')?.isValid()) {
        context.addIssue({ code: 'custom', path: ['contact_value'], message: 'Ingresá un WhatsApp válido con código de área.' })
    }
})

type Values = z.infer<typeof prefillSchema>

export function PacoPrefillReview({
    prefill,
    disabled,
    onSubmit,
}: {
    prefill: PacoPrefill
    disabled: boolean
    onSubmit: (action: PacoAction) => Promise<unknown>
}) {
    const { register, handleSubmit, watch, formState: { errors } } = useForm<Values>({
        resolver: zodResolver(prefillSchema),
        defaultValues: {
            initial_query: prefill.initial_query || '',
            name: prefill.name || '',
            contact_channel: prefill.contact_channel || (prefill.phone ? 'whatsapp' : 'email'),
            contact_value: prefill.email || prefill.phone || '',
        },
    })
    const channel = watch('contact_channel')

    return (
        <form className="paco-prefill" onSubmit={handleSubmit((values) => onSubmit({ type: 'confirm_prefill', values }))}>
            <div className="paco-prefill__heading">
                <span>Enlace personalizado</span>
                <p>Revisá la información antes de comenzar.</p>
            </div>
            <div className="paco-field">
                <label htmlFor="paco-prefill-query">Consulta</label>
                <textarea id="paco-prefill-query" rows={4} {...register('initial_query')} />
                {errors.initial_query && <p className="paco-field__error">{errors.initial_query.message}</p>}
            </div>
            <div className="paco-prefill__grid">
                <div className="paco-field">
                    <label htmlFor="paco-prefill-name">Nombre</label>
                    <input id="paco-prefill-name" autoComplete="name" {...register('name')} />
                    {errors.name && <p className="paco-field__error">{errors.name.message}</p>}
                </div>
                <div className="paco-field">
                    <label htmlFor="paco-prefill-channel">Canal</label>
                    <select id="paco-prefill-channel" {...register('contact_channel')}>
                        <option value="email">Email</option>
                        <option value="whatsapp">WhatsApp</option>
                    </select>
                </div>
            </div>
            <div className="paco-field">
                <label htmlFor="paco-prefill-contact">{channel === 'email' ? 'Email' : 'WhatsApp'}</label>
                <input
                    id="paco-prefill-contact"
                    type={channel === 'email' ? 'email' : 'tel'}
                    autoComplete={channel === 'email' ? 'email' : 'tel'}
                    {...register('contact_value')}
                />
                {errors.contact_value && <p className="paco-field__error">{errors.contact_value.message}</p>}
            </div>
            <button className="paco-submit" type="submit" disabled={disabled}>
                {disabled ? 'Confirmando…' : 'Confirmar y comenzar'}
            </button>
        </form>
    )
}
