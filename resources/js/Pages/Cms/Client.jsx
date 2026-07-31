import { Head } from '@inertiajs/react'
import { useLayoutEffect, useRef } from 'react'
import BlocksList from '@/Components/BlocksList'
import ClientHero from '@/Components/ClientCase/ClientHero'
import ClientNavigation from '@/Components/ClientCase/ClientNavigation'
import DefaultLayout from '@/Layouts/Default'
import { initClientCaseAnimations } from '@/gsap-animations'

export default function Client({ client, blocks = [], route }) {
    const caseRef = useRef(null)

    useLayoutEffect(() => initClientCaseAnimations(caseRef.current), [blocks.length, client?.id])

    return (
        <>
            <Head title={client?.title} />
            <DefaultLayout route={route}>
                <article ref={caseRef} className="bg-black text-white">
                    <ClientHero client={client} />
                    <BlocksList blocks={blocks} client={client} />
                    <ClientNavigation navigation={client?.navigation} />
                </article>
            </DefaultLayout>
        </>
    )
}
