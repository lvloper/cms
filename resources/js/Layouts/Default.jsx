import { Head, usePage } from '@inertiajs/react'
import SiteFooter from '@/Components/SiteFooter'

export default function DefaultLayout({ children, route, layout = 'default' }) {
    const hasIndex = layout === 'hasIndex'
    const index = usePage().props.index ?? null

    return (
        <>
            <Head>
                {route?.custom_css && <style>{route.custom_css}</style>}
                {route?.header_scripts && <script dangerouslySetInnerHTML={{ __html: route.header_scripts }} />}
            </Head>

            <div className="font-sans text-base tracking-normal leading-normal text-white bg-black frontend">
                <Header route={route} />
                <div id="main" className={hasIndex ? 'has-sidebar' : ''}>
                    {hasIndex && <Sidebar index={index} />}
                    <div className="z-10 main-content">{children}</div>
                </div>
                <SiteFooter />
                {route?.footer_scripts && <script dangerouslySetInnerHTML={{ __html: route.footer_scripts }} />}
            </div>
        </>
    )
}

function Header({ route }) {
    const { shared } = usePage().props
    const menu = shared?.menu ?? []

    return (
        <header className="relative z-50 bg-black border-b border-gray-800">
            <div className="container mx-auto px-4">
                <div className="flex items-center justify-between h-16 md:h-20">
                    <a href="/" className="text-xl font-bold text-white">
                        {import.meta.env.VITE_APP_NAME || 'CMS'}
                    </a>
                    {menu.length > 0 && (
                        <nav className="hidden md:flex items-center gap-6">
                            {menu.map((item, i) => (
                                <a key={i} href={item.url} className="text-sm font-medium text-gray-400 hover:text-white transition-colors">
                                    {item.title}
                                </a>
                            ))}
                        </nav>
                    )}
                </div>
            </div>
        </header>
    )
}

function Sidebar({ index }) {
    if (!index || index.length === 0) return null

    return (
        <aside className="sidebar p-6 bg-gray-50 border-r border-gray-100">
            <nav className="space-y-2">
                {index.map((item, i) => (
                    <a
                        key={i}
                        href={`#${item.id}`}
                        className="block text-sm text-gray-600 hover:text-gray-900 transition-colors"
                    >
                        {item.title}
                    </a>
                ))}
            </nav>
        </aside>
    )
}
