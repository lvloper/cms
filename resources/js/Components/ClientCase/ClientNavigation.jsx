import { useEffect, useRef } from 'react'
import { createRoot } from 'react-dom/client'
import tippy, { followCursor } from 'tippy.js'
import { ClientStoryCard } from '@/Components/ClientStoryPopup'

function ClientReferenceLink({ client, direction }) {
    const triggerRef = useRef(null)
    const tippyRef = useRef(null)

    useEffect(() => {
        const content = document.createElement('div')
        const contentRoot = createRoot(content)
        const renderContent = (isActive) => {
            contentRoot.render(<ClientStoryCard client={client} isActive={isActive} />)
        }

        renderContent(false)

        const instance = tippy(triggerRef.current, {
            appendTo: () => document.body,
            arrow: false,
            content,
            delay: [100, 40],
            duration: [160, 100],
            followCursor: true,
            hideOnClick: true,
            maxWidth: 'none',
            offset: [0, 24],
            onHidden: () => renderContent(false),
            onShow: () => renderContent(true),
            placement: 'top',
            plugins: [followCursor],
            theme: 'client-story',
            touch: false,
        })
        tippyRef.current = instance

        return () => {
            tippyRef.current = null
            instance.destroy()
            contentRoot.unmount()
        }
    }, [client])

    const label = direction === 'previous' ? 'Anterior' : 'Siguiente'
    const arrow = direction === 'previous' ? '←' : '→'

    return (
        <a
            ref={triggerRef}
            href={client.url}
            className={`client-navigation__link${direction === 'next' ? ' client-navigation__link--next' : ''}`}
            style={{ '--client-reference-color': client.color || 'var(--color-socies-green)' }}
            aria-label={`${label}: ${client.title}`}
            aria-haspopup="dialog"
            onBlur={() => tippyRef.current?.hide()}
            onFocus={() => tippyRef.current?.show()}
            onMouseEnter={() => {
                if (window.matchMedia('(hover: hover)').matches) {
                    tippyRef.current?.show()
                }
            }}
            onMouseLeave={() => tippyRef.current?.hide()}
        >
            <span className="client-navigation__eyebrow">{direction === 'previous' ? `${arrow} ${label}` : `${label} ${arrow}`}</span>
            <strong className="client-navigation__title">{client.title}</strong>
        </a>
    )
}

export default function ClientNavigation({ navigation }) {
    if (!navigation?.previous || !navigation?.next) return null

    return (
        <nav className="client-navigation container mx-auto px-4 py-8 md:py-12" aria-label="Navegación entre clientes">
            <div className="client-navigation__inner">
                <ClientReferenceLink client={navigation.previous} direction="previous" />
                <ClientReferenceLink client={navigation.next} direction="next" />
            </div>
        </nav>
    )
}
