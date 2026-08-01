import { Head, usePage } from '@inertiajs/react'
import SiteFooter from '@/Components/SiteFooter'
import SiteHeader from '@/Components/SiteHeader'

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
                <SiteHeader visible />
                <div id="main" className={`pt-[var(--header-height)]${hasIndex ? ' has-sidebar' : ''}`}>
                    {hasIndex && <Sidebar index={index} />}
                    <div className="z-10 main-content">{children}</div>
                </div>
                <SiteFooter />
                {route?.footer_scripts && <script dangerouslySetInnerHTML={{ __html: route.footer_scripts }} />}
            </div>
        </>
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
