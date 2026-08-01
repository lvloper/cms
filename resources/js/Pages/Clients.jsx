import { Head } from '@inertiajs/react'
import ClientStoriesGrid from '@/Components/ClientStoriesGrid'
import DefaultLayout from '@/Layouts/Default'

export default function Clients({ clients }) {
    return (
        <>
            <Head title="Clientes" />
            <DefaultLayout route={{ title: 'Clientes' }}>
                <ClientStoriesGrid clients={clients} />
            </DefaultLayout>
        </>
    )
}
