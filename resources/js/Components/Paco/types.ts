export type PacoOption = {
    value: string
    label: string
    allow_detail?: boolean
    detail_label?: string
    detail_placeholder?: string
    detail_required?: boolean
    detail_max_length?: number
}

export type PacoPart = {
    type: 'text_input' | 'single_select' | 'multi_select' | 'contact_form' | 'date' | 'content_carousel'
    id: string
    required?: boolean
    allow_skip?: boolean
    multiline?: boolean
    max_length?: number
    placeholder?: string
    options?: PacoOption[]
    fields?: Array<Record<string, unknown>>
    items?: PacoEvidenceItem[]
    reason_code?: string
}

export type PacoEvidenceItem = {
    item_id: string
    entity_type: 'work' | 'testimonial'
    entity_id: number
    client_name: string
    title?: string
    problem?: string
    solution?: string
    result?: string
    quote?: string
    author?: string
    role?: string
    url?: string | null
    image_url?: string | null
    client_logo_url?: string | null
}

export type PacoTurn = {
    id: string
    actor: 'assistant' | 'user'
    message: string
    parts: PacoPart[]
    meta?: Record<string, unknown>
    created_at?: string | null
}

export type PacoPrefill = {
    name?: string
    email?: string
    phone?: string
    contact_channel?: 'email' | 'whatsapp'
    initial_query?: string
    requires_confirmation: boolean
}

export type PacoConversationState = {
    conversation_id: string
    conversation_token?: string
    version: number
    status: 'active' | 'closed' | 'blocked'
    stage: string
    campaign?: { code: string; name: string } | null
    turns: PacoTurn[]
    turn?: Omit<PacoTurn, 'actor'> | null
    prefill?: PacoPrefill | null
}

export type PacoEntry = {
    campaign: string
    prefillToken?: string | null
    utmSource?: string | null
    utmMedium?: string | null
    utmCampaign?: string | null
}

export type PacoAction = {
    type: string
    component_id?: string
    value?: unknown
    values?: Record<string, unknown>
}
