import { createRoot } from 'react-dom/client'
import { QueryClient, QueryClientProvider } from '@tanstack/react-query'
import SociesConversation from './Components/Paco/SociesConversation'
import '../css/app.css'

const queryClient = new QueryClient({
    defaultOptions: {
        queries: { retry: 1, staleTime: 30_000 },
        mutations: { retry: false },
    },
})

const mount = () => {
    const element = document.querySelector('[data-paco-read-only]')
    if (!element) return

    const state = JSON.parse(element.dataset.pacoState || 'null')
    createRoot(element).render(
        <QueryClientProvider client={queryClient}>
            <SociesConversation
                mode="page"
                readOnly
                readOnlyState={state}
                entry={{ campaign: state?.campaign?.code || 'direct_default' }}
            />
        </QueryClientProvider>,
    )
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', mount, { once: true })
} else {
    mount()
}
