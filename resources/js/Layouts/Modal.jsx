import BlocksList from '@/Components/BlocksList'
import DefaultLayout from './Default'

export default function ModalLayout({ children, route, parentBlocks }) {
    return (
        <>
            <DefaultLayout route={route}>
                <div className="relative">
                    <div className="z-10 bg-white main-content">
                        {parentBlocks && <BlocksList blocks={parentBlocks} />}
                    </div>
                </div>
            </DefaultLayout>
            <ModalOverlay>
                {children}
            </ModalOverlay>
        </>
    )
}

function ModalOverlay({ children }) {
    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div className="bg-white rounded-xl shadow-2xl max-w-3xl w-full max-h-[90vh] overflow-y-auto p-6">
                {children}
            </div>
        </div>
    )
}
