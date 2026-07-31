import { useMutation } from '@tanstack/react-query'
import { useEffect, useRef, useState } from 'react'
import { createConversation, getConversation, sendAction } from './pacoApi'
import type { PacoAction, PacoConversationState, PacoEntry } from './types'

type StoredConversation = { id: string; token: string; version: number }

export function usePacoConversation(
    entry: PacoEntry,
    enabled: boolean,
    initialState: PacoConversationState | null = null,
) {
    const [state, setState] = useState<PacoConversationState | null>(initialState)
    const [token, setToken] = useState<string | null>(null)
    const initialized = useRef(false)
    const sourceKey = entry.pageContext?.contentType === 'client'
        ? `client-${entry.pageContext.contentId}`
        : 'direct'
    const storageKey = `socies-conversation:${entry.campaign}:${sourceKey}`

    const createMutation = useMutation({
        mutationFn: () => createConversation(entry),
        onSuccess: (created) => {
            const nextToken = created.conversation_token || null
            setState(created)
            setToken(nextToken)
            if (nextToken) {
                const stored: StoredConversation = {
                    id: created.conversation_id,
                    token: nextToken,
                    version: created.version,
                }
                sessionStorage.setItem(storageKey, JSON.stringify(stored))
            }
        },
    })

    const restoreMutation = useMutation({
        mutationFn: (stored: StoredConversation) => getConversation(stored.id, stored.token),
        onSuccess: (restored, stored) => {
            setToken(stored.token)
            setState(restored)
        },
        onError: () => {
            sessionStorage.removeItem(storageKey)
            createMutation.mutate()
        },
    })

    useEffect(() => {
        if (!enabled || initialized.current) return
        initialized.current = true

        if (!entry.prefillToken) {
            const raw = sessionStorage.getItem(storageKey)
            if (raw) {
                try {
                    const stored = JSON.parse(raw) as StoredConversation
                    if (stored.id && stored.token) {
                        restoreMutation.mutate(stored)
                        return
                    }
                } catch {
                    sessionStorage.removeItem(storageKey)
                }
            }
        }

        createMutation.mutate()
    }, [enabled, entry.prefillToken, storageKey])

    const actionMutation = useMutation({
        mutationFn: (action: PacoAction) => {
            if (!state || !token) throw new Error('La conversación todavía no está lista.')
            return sendAction(state, token, action)
        },
        onSuccess: (updated) => {
            setState(updated)
            if (!token) return
            sessionStorage.setItem(storageKey, JSON.stringify({
                id: updated.conversation_id,
                token,
                version: updated.version,
            } satisfies StoredConversation))
        },
    })

    return {
        state,
        prefill: state?.prefill || null,
        isStarting: createMutation.isPending || restoreMutation.isPending,
        isSending: actionMutation.isPending,
        error: createMutation.error || actionMutation.error,
        startAgain: () => {
            setState(null)
            setToken(null)
            sessionStorage.removeItem(storageKey)
            createMutation.reset()
            restoreMutation.reset()
            actionMutation.reset()
            createMutation.mutate()
        },
        send: actionMutation.mutateAsync,
        retryStart: () => {
            createMutation.reset()
            createMutation.mutate()
        },
    }
}
