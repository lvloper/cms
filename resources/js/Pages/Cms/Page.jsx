import { Head } from '@inertiajs/react'
import DefaultLayout from '@/Layouts/Default'
import HomeLayout from '@/Layouts/Home'
import ModalLayout from '@/Layouts/Modal'
import BlocksList from '@/Components/BlocksList'

export default function Page({ blocks, route, layout = 'default', parentBlocks, isModal }) {
    const LayoutComponent = layout === 'home' ? HomeLayout
        : layout === 'modal' ? ModalLayout
        : DefaultLayout

    const layoutProps = layout === 'modal'
        ? { route, parentBlocks }
        : { route, layout }

    return (
        <>
            <Head title={route?.title} />
            <LayoutComponent {...layoutProps}>
                <BlocksList blocks={blocks} />
            </LayoutComponent>
        </>
    )
}
