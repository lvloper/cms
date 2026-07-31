import { Head } from '@inertiajs/react'
import SiteHeader from '@/Components/SiteHeader'
import SiteFooter from '@/Components/SiteFooter'

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
                <SiteFooter />
                {route?.footer_scripts && <script dangerouslySetInnerHTML={{ __html: route.footer_scripts }} />}
            </div>
        </>
    )
}
