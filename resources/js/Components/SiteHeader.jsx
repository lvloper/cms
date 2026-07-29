import { Link } from '@inertiajs/react'
import SociesLogo from '@/Components/SociesLogo'

export default function SiteHeader() {
    return (
        <header className="site-header" data-site-header>
            <div className="site-header__inner">
                <Link
                    href="/"
                    className="site-header__brand"
                    aria-label="Socies — Inicio"
                    data-header-logo-target
                >
                    <SociesLogo variant="inverse" />
                </Link>
            </div>
            <div className="site-header__line" data-header-line aria-hidden="true" />
        </header>
    )
}
