import { z } from 'zod'
import type { PacoAction, PacoConversationState, PacoEntry } from './types'

const optionSchema = z.object({
    value: z.string(),
    label: z.string(),
    allow_detail: z.boolean().optional(),
    detail_label: z.string().optional(),
    detail_placeholder: z.string().optional(),
    detail_required: z.boolean().optional(),
    detail_max_length: z.number().optional(),
})
const evidenceItemSchema = z.object({
    item_id: z.string(),
    entity_type: z.enum(['work', 'testimonial']),
    entity_id: z.number(),
    client_name: z.string(),
    title: z.string().optional(),
    problem: z.string().optional(),
    solution: z.string().optional(),
    result: z.string().optional(),
    quote: z.string().optional(),
    author: z.string().optional(),
    role: z.string().optional(),
    url: z.string().nullable().optional(),
    image_url: z.string().nullable().optional(),
    client_logo_url: z.string().nullable().optional(),
})
const partSchema = z.object({
    type: z.enum(['text_input', 'single_select', 'multi_select', 'contact_form', 'date', 'content_carousel']),
    id: z.string(),
    required: z.boolean().optional(),
    allow_skip: z.boolean().optional(),
    multiline: z.boolean().optional(),
    max_length: z.number().optional(),
    placeholder: z.string().optional(),
    options: z.array(optionSchema).optional(),
    fields: z.array(z.record(z.string(), z.unknown())).optional(),
    items: z.array(evidenceItemSchema).max(4).optional(),
    reason_code: z.string().optional(),
})
const turnSchema = z.object({
    id: z.string(),
    actor: z.enum(['assistant', 'user']),
    message: z.string(),
    parts: z.array(partSchema).default([]),
    meta: z.record(z.string(), z.unknown()).optional(),
    created_at: z.string().nullable().optional(),
})
const stateSchema = z.object({
    conversation_id: z.string(),
    conversation_token: z.string().optional(),
    version: z.number(),
    status: z.enum(['active', 'closed', 'blocked']),
    stage: z.string(),
    campaign: z.object({ code: z.string(), name: z.string() }).nullable().optional(),
    turns: z.array(turnSchema),
    prefill: z.object({
        name: z.string().optional(),
        email: z.string().optional(),
        phone: z.string().optional(),
        contact_channel: z.enum(['email', 'whatsapp']).optional(),
        initial_query: z.string().optional(),
        requires_confirmation: z.boolean(),
    }).nullable().optional(),
}).passthrough()

export class PacoApiError extends Error {
    constructor(message: string, public readonly status: number) {
        super(message)
        this.name = 'PacoApiError'
    }
}

async function jsonRequest(url: string, init: RequestInit): Promise<PacoConversationState> {
    const response = await fetch(url, {
        ...init,
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            ...init.headers,
        },
    })
    const payload = await response.json().catch(() => ({}))

    if (!response.ok) {
        const validation = payload?.errors ? Object.values(payload.errors).flat().join(' ') : null
        const message = response.status === 429
            ? 'Gracias por escribirnos. Ya registramos tu contacto y nuestro equipo se va a comunicar con vos.'
            : response.status === 409
                ? 'La conversación se actualizó. Intentemos continuar desde el último mensaje.'
                : validation || 'No pudimos procesar la solicitud. Intentá nuevamente.'
        throw new PacoApiError(message, response.status)
    }

    return stateSchema.parse(payload) as PacoConversationState
}

export function createConversation(entry: PacoEntry): Promise<PacoConversationState> {
    return jsonRequest('/api/paco/conversations', {
        method: 'POST',
        body: JSON.stringify({
            campaign: entry.campaign,
            prefill_token: entry.prefillToken || null,
            origin_url: window.location.href,
            referrer: document.referrer || null,
            locale: document.documentElement.lang || 'es-AR',
            utm_source: entry.utmSource || null,
            utm_medium: entry.utmMedium || null,
            utm_campaign: entry.utmCampaign || null,
            page_context: entry.pageContext ? {
                content_type: entry.pageContext.contentType,
                content_id: entry.pageContext.contentId,
            } : null,
        }),
    })
}

export function sendAction(
    state: PacoConversationState,
    token: string,
    action: PacoAction,
): Promise<PacoConversationState> {
    return jsonRequest(`/api/paco/conversations/${state.conversation_id}/actions`, {
        method: 'POST',
        headers: {
            Authorization: `Bearer ${token}`,
            'Idempotency-Key': crypto.randomUUID(),
        },
        body: JSON.stringify({
            conversation_version: state.version,
            action,
            turn_context: { visible_content_ids: [] },
        }),
    })
}

export function getConversation(id: string, token: string): Promise<PacoConversationState> {
    return jsonRequest(`/api/paco/conversations/${id}`, {
        method: 'GET',
        headers: { Authorization: `Bearer ${token}` },
    })
}
