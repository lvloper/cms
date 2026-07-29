import { Head } from '@inertiajs/react'
import HomeLayout from '@/Layouts/Home'
import HomeHero from '@/Components/HomeHero'
import ClientLogosMarquee from '@/Components/ClientLogosMarquee'

const STATIC_CLIENT_LOGOS = [
    {
        id: 'fundacion-huesped',
        src: '/storage/images/clients/logos/fundacion-huesped.webp',
        alt: 'Fundación Huésped',
        title: 'Fundación Huésped',
        color: '#00A7A0',
        popupTextColor: 'black',
    },
    {
        id: 'amnistia-internacional',
        src: '/storage/images/clients/logos/amnistia-internacional.webp',
        alt: 'Amnistía Internacional',
        title: 'Amnistía Internacional',
        color: '#FFF200',
        popupTextColor: 'black',
    },
    {
        id: 'eurobursatil',
        src: '/storage/images/clients/logos/eurobursatil.webp',
        alt: 'Eurobursatil',
        title: 'Eurobursatil',
        color: '#23408E',
        popupTextColor: 'white',
    },
    {
        id: 'qyt-servicios',
        src: '/storage/images/clients/logos/qyt-servicios.webp',
        alt: 'QyT Servicios',
        title: 'QyT Servicios',
        color: '#2D7D8A',
        popupTextColor: 'white',
    },
    {
        id: 'fundacion-leloir',
        src: '/storage/images/clients/logos/fundacion-leloir.webp',
        alt: 'Fundación Leloir',
        title: 'Fundación Leloir',
        color: '#713C8C',
        popupTextColor: 'white',
    },
    {
        id: 'cedes',
        src: '/storage/images/clients/logos/cedes.webp',
        alt: 'CEDES',
        title: 'CEDES',
        color: '#E7772E',
        popupTextColor: 'black',
    },
    {
        id: 'iidi',
        src: '/storage/images/clients/logos/iidi.webp',
        alt: 'IIDI',
        title: 'IIDI',
        color: '#E34D61',
        popupTextColor: 'black',
    },
]

export default function Home({ clients }) {
    const clientLogos = clients ?? STATIC_CLIENT_LOGOS

    return (
        <>
            <Head title="Home" />
            <HomeLayout route={{ title: 'Home' }}>
                <HomeHero />
                <ClientLogosMarquee clients={clientLogos} />
            </HomeLayout>
        </>
    )
}
