import { useEffect, useRef } from 'react'
import { createRoot } from 'react-dom/client'
import tippy, { followCursor } from 'tippy.js'
import 'tippy.js/dist/tippy.css'
import SociesLogo from '@/Components/SociesLogo'
import { siteNavigationItems } from '@/Components/siteNavigation'

function SiteNavigation() {
    return (
        <nav className="site-navigation" aria-label="Navegación principal">
            <ul className="site-navigation__list">
                {siteNavigationItems.map((item) => (
                    <li key={item.href}>
                        <a className="site-navigation__link" href={item.href}>
                            {item.label}
                        </a>
                    </li>
                ))}
            </ul>
        </nav>
    )
}

export default function SiteHeader({ visible = false }) {
    const headerRef = useRef(null)

    useEffect(() => {
        const trigger = headerRef.current

        if (!trigger) {
            return undefined
        }

        const content = document.createElement('div')
        const contentRoot = createRoot(content)
        contentRoot.render(<SiteNavigation />)

        const instance = tippy(trigger, {
            appendTo: () => document.body,
            arrow: false,
            content,
            delay: [100, 40],
            duration: [160, 100],
            followCursor: true,
            hideOnClick: true,
            interactive: true,
            maxWidth: 'none',
            offset: [0, 24],
            placement: 'top',
            plugins: [followCursor],
            theme: 'site-navigation',
            trigger: 'mouseenter focus',
        })

        return () => {
            instance.destroy()
            contentRoot.unmount()
        }
    }, [])

    return (
        <header
            ref={headerRef}
            className={`site-header${visible ? ' site-header--visible' : ''}`}
            data-site-header
            role="button"
            tabIndex="0"
            aria-label="Abrir navegación"
        >
            <div className="site-header__inner">
                <div
                    className="site-header__brand"
                    data-header-logo-target
                    aria-hidden="true"
                >
                    <SociesLogo variant="inverse" />
                </div>
            </div>
            <div className="site-header__line" data-header-line aria-hidden="true" />
        </header>
    )
}
