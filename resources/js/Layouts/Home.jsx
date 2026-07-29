import { Head } from '@inertiajs/react'
import SiteHeader from '@/Components/SiteHeader'

export default function HomeLayout({ children, route }) {
    return (
        <>
            <Head>
                {route?.custom_css && <style>{route.custom_css}</style>}
                {route?.header_scripts && <script dangerouslySetInnerHTML={{ __html: route.header_scripts }} />}
            </Head>
            <div className="font-sans text-base tracking-normal leading-normal text-white bg-black frontend">
                <SiteHeader />
                <main className="relative z-10 bg-black main-content">{children}</main>
                <Footer route={route} />
            </div>
        </>
    )
}

function Footer({ route }) {
    return (
        <footer className="bg-black border-t border-white/20 py-8">
            <div className="container mx-auto px-4">
                <p className="text-sm text-white/60 text-center">
                    {import.meta.env.VITE_APP_NAME || 'CMS'} &copy; {new Date().getFullYear()}
                </p>
                {route?.footer_scripts && <script dangerouslySetInnerHTML={{ __html: route.footer_scripts }} />}
            </div>
        </footer>
    )
}
