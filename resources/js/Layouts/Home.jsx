import { Head, usePage } from '@inertiajs/react'
import DefaultLayout from './Default'

export default function HomeLayout({ children, route }) {
    return (
        <>
            <Head>
                {route?.custom_css && <style>{route.custom_css}</style>}
                {route?.header_scripts && <script dangerouslySetInnerHTML={{ __html: route.header_scripts }} />}
            </Head>
            <div className="font-sans text-base tracking-normal leading-normal text-gray-800 frontend">
                <div className="z-10 bg-white main-content">{children}</div>
                <Footer route={route} />
            </div>
        </>
    )
}

function Footer({ route }) {
    return (
        <footer className="bg-gray-50 border-t border-gray-100 py-8">
            <div className="container mx-auto px-4">
                <p className="text-sm text-gray-500 text-center">
                    {import.meta.env.VITE_APP_NAME || 'CMS'} &copy; {new Date().getFullYear()}
                </p>
                {route?.footer_scripts && <script dangerouslySetInnerHTML={{ __html: route.footer_scripts }} />}
            </div>
        </footer>
    )
}
