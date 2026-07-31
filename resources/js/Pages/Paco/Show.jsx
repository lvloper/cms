import { Head } from '@inertiajs/react'
import HomeLayout from '@/Layouts/Home'
import SociesConversation from '@/Components/Paco/SociesConversation'

export default function PacoShow({ entry }) {
    return (
        <>
            <Head title="Hablemos" />
            <HomeLayout route={{ title: 'Hablemos' }}>
                <SociesConversation mode="page" entry={entry} />
            </HomeLayout>
        </>
    )
}
