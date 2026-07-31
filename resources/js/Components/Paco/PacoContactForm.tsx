import { zodResolver } from '@hookform/resolvers/zod'
import { parsePhoneNumberFromString } from 'libphonenumber-js'
import { useForm } from 'react-hook-form'
import { z } from 'zod'
import type { PacoAction } from './types'

const contactSchema = z.object({
    name: z.string().trim().min(2, 'Ingresá tu nombre.'),
    channel: z.enum(['email', 'whatsapp']),
    contact_value: z.string().trim().min(5, 'Ingresá un dato de contacto.'),
}).superRefine((value, context) => {
    if (value.channel === 'email' && !z.email().safeParse(value.contact_value).success) {
        context.addIssue({ code: 'custom', path: ['contact_value'], message: 'Ingresá un email válido.' })
    }
    if (value.channel === 'whatsapp' && !parsePhoneNumberFromString(value.contact_value, 'AR')?.isValid()) {
        context.addIssue({ code: 'custom', path: ['contact_value'], message: 'Ingresá un WhatsApp válido con código de área.' })
    }
})

type ContactValues = z.infer<typeof contactSchema>

type Props = {
    disabled: boolean
    onSubmit: (action: PacoAction) => Promise<unknown>
}

export function PacoContactForm({ disabled, onSubmit }: Props) {
    const { register, handleSubmit, watch, formState: { errors } } = useForm<ContactValues>({
        resolver: zodResolver(contactSchema),
        defaultValues: { channel: 'email' },
    })
    const channel = watch('channel')

    return (
        <form
            className="paco-form"
            onSubmit={handleSubmit(async (value) => {
                await onSubmit({ type: 'contact_submit', component_id: 'contact', value })
            })}
        >
            <div className="paco-field">
                <label htmlFor="paco-contact-name">Tu nombre</label>
                <input id="paco-contact-name" autoComplete="name" {...register('name')} />
                {errors.name && <p className="paco-field__error">{errors.name.message}</p>}
            </div>
            <fieldset className="paco-fieldset">
                <legend>¿Cómo preferís que te contactemos?</legend>
                <div className="paco-choice-row">
                    <label className="paco-radio">
                        <input type="radio" value="email" {...register('channel')} />
                        <span>Email</span>
                    </label>
                    <label className="paco-radio">
                        <input type="radio" value="whatsapp" {...register('channel')} />
                        <span>WhatsApp</span>
                    </label>
                </div>
            </fieldset>
            <div className="paco-field">
                <label htmlFor="paco-contact-value">{channel === 'email' ? 'Email' : 'WhatsApp'}</label>
                <input
                    id="paco-contact-value"
                    type={channel === 'email' ? 'email' : 'tel'}
                    autoComplete={channel === 'email' ? 'email' : 'tel'}
                    placeholder={channel === 'email' ? 'nombre@organizacion.com' : '+54 11 0000 0000'}
                    {...register('contact_value')}
                />
                {errors.contact_value && <p className="paco-field__error">{errors.contact_value.message}</p>}
            </div>
            <p className="paco-form__privacy">Usaremos estos datos únicamente para responder esta consulta.</p>
            <button className="paco-submit" type="submit" disabled={disabled}>
                {disabled ? 'Enviando…' : 'Compartir y continuar'}
            </button>
        </form>
    )
}
