import { Head } from '@inertiajs/react'
import DefaultLayout from '@/Layouts/Default'

export default function Search({ route, results = [], query = '' }) {
    return (
        <>
            <Head title={`Buscar: ${query}`} />
            <DefaultLayout route={route}>
                <section className="py-12 md:py-16">
                    <div className="container mx-auto px-4">
                        <div className="mx-auto max-w-3xl">
                            <h1 className="text-3xl md:text-4xl font-bold text-gray-900 mb-2">
                                Resultados para "{query}"
                            </h1>
                            <p className="text-gray-500 mb-8">{results.length} resultados</p>
                            {results.length > 0 ? (
                                <ul className="space-y-6">
                                    {results.map((item, i) => (
                                        <li key={i} className="border-b border-gray-100 pb-6">
                                            <a href={item.url} className="block group">
                                                <h2 className="text-lg font-semibold text-primary group-hover:underline">
                                                    {item.title}
                                                </h2>
                                                {item.description && (
                                                    <p className="text-sm text-gray-600 mt-1">{item.description}</p>
                                                )}
                                            </a>
                                        </li>
                                    ))}
                                </ul>
                            ) : (
                                <p className="text-gray-500">No se encontraron resultados.</p>
                            )}
                        </div>
                    </div>
                </section>
            </DefaultLayout>
        </>
    )
}
