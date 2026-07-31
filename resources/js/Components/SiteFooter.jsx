import { Link, usePage } from '@inertiajs/react'
import SociesLogo from '@/Components/SociesLogo'

const footerLinks = [
    { label: 'Clientes', href: '/#clientes' },
    { label: 'Qué hacemos', href: '/#hablemos' },
]

export default function SiteFooter() {
    const { shared } = usePage().props
    const social = shared?.social ?? {}

    return (
        <footer className="border-t border-white/20 bg-black py-12 text-white md:py-16">
            <div className="container mx-auto">
                <div className="grid items-center gap-8 md:grid-cols-12 md:gap-6">
                    <Link
                        href="/"
                        className="block w-36 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-focus md:col-span-4 md:w-40"
                        aria-label="Socies — Inicio"
                    >
                        <SociesLogo variant="inverse" />
                    </Link>

                    <nav aria-label="Navegación del pie" className="md:col-span-4">
                        <ul className="flex flex-wrap gap-x-8 gap-y-3 text-sm font-medium">
                            {footerLinks.map((link) => (
                                <li key={link.href}>
                                    <Link
                                        href={link.href}
                                        className="transition-colors hover:text-socies-green focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-focus"
                                    >
                                        {link.label}
                                    </Link>
                                </li>
                            ))}
                        </ul>
                    </nav>

                    <nav aria-label="Redes sociales" className="md:col-span-4 md:justify-self-end">
                        <ul className="flex flex-wrap gap-x-8 gap-y-3 text-sm font-medium">
                            <li>
                                <a
                                    href={social.linkedin || '#'}
                                    target="_blank"
                                    rel="noreferrer"
                                    className="transition-colors hover:text-socies-green focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-focus"
                                >
                                    LinkedIn
                                </a>
                            </li>
                            <li>
                                <a
                                    href={social.instagram || '#'}
                                    target="_blank"
                                    rel="noreferrer"
                                    className="transition-colors hover:text-socies-green focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-focus"
                                >
                                    Instagram
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>

                <div className="mt-12 border-t border-white/20 pt-4 text-xs text-gray-2">
                    <p>© {new Date().getFullYear()} Socies</p>
                </div>
            </div>
        </footer>
    )
}
