import { useEffect, useRef, useState } from 'react'
import { createRoot } from 'react-dom/client'
import tippy, { followCursor } from 'tippy.js'
import 'tippy.js/dist/tippy.css'
import {
    ClientStoryCard,
    ClientStoryModal,
} from '@/Components/ClientStoryPopup'

const MARQUEE_DURATION = '40s'

function ClientLogo({ client, hidden, onOpen }) {
    const [isMobile, setIsMobile] = useState(false)
    const Trigger = hidden ? 'span' : isMobile ? 'button' : 'a'
    const triggerRef = useRef(null)
    const tippyRef = useRef(null)

    useEffect(() => {
        const mediaQuery = window.matchMedia('(max-width: 767px)')
        const updateMobile = () => setIsMobile(mediaQuery.matches)
        updateMobile()
        mediaQuery.addEventListener('change', updateMobile)

        const content = document.createElement('div')
        const contentRoot = createRoot(content)
        const renderContent = (isActive) => {
            contentRoot.render(
                <ClientStoryCard client={client} isActive={isActive} />,
            )
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
            mediaQuery.removeEventListener('change', updateMobile)
            tippyRef.current = null
            instance.destroy()
            contentRoot.unmount()
        }
    }, [client, isMobile])

    return (
        <Trigger
            ref={triggerRef}
            type={!hidden && isMobile ? 'button' : undefined}
            href={!hidden && !isMobile ? client.url : undefined}
            tabIndex={hidden ? -1 : undefined}
            className="home-client-logos__trigger"
            aria-label={hidden ? undefined : `Ver historia de ${client.alt}`}
            aria-haspopup={hidden ? undefined : 'dialog'}
            onBlur={() => tippyRef.current?.hide()}
            onClick={(event) => {
                if (!hidden && !isMobile) {
                    return
                }

                event.preventDefault()
                tippyRef.current?.hide()
                onOpen(client)
            }}
            onFocus={() => tippyRef.current?.show()}
            onMouseEnter={() => {
                if (window.matchMedia('(hover: hover)').matches) {
                    tippyRef.current?.show()
                }
            }}
            onMouseLeave={() => tippyRef.current?.hide()}
        >
            <span
                aria-hidden="true"
                className="home-client-logos__logo"
                style={{
                    '--client-logo-color': 'var(--color-white)',
                    '--client-logo-mask': `url("${client.src}")`,
                }}
            />
        </Trigger>
    )
}

function LogoGroup({
    clients,
    hidden = false,
    onOpen,
}) {
    return (
        <ul
            aria-hidden={hidden || undefined}
            className="home-client-logos__group"
        >
            {clients.map((client) => (
                <li className="home-client-logos__item" key={`${client.id}-${hidden}`}>
                    <ClientLogo
                        client={client}
                        hidden={hidden}
                        onOpen={onOpen}
                    />
                </li>
            ))}
        </ul>
    )
}

export default function ClientLogosMarquee({ clients = [] }) {
    const [activeClient, setActiveClient] = useState(null)

    if (clients.length === 0) {
        return null
    }

    return (
        <section
            aria-labelledby="client-logos-title"
            className="home-client-logos"
            data-client-logos
            style={{ '--home-client-logos-duration': MARQUEE_DURATION }}
        >
            <h2 id="client-logos-title" className="sr-only">
                Clientes con los que trabajamos
            </h2>

            <div className="home-client-logos__mask">
                <div className="home-client-logos__viewport">
                    <div className="home-client-logos__track">
                        <LogoGroup
                            clients={clients}
                            onOpen={setActiveClient}
                        />
                        <LogoGroup
                            clients={clients}
                            hidden
                            onOpen={setActiveClient}
                        />
                    </div>
                </div>
            </div>

            {activeClient && (
                <ClientStoryModal
                    client={activeClient}
                    onClose={() => setActiveClient(null)}
                />
            )}
        </section>
    )
}
