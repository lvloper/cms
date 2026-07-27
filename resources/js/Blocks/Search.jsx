import { useState, useCallback } from 'react'
import { router } from '@inertiajs/react'

export default function SearchBlock({ title = 'Buscar', description = 'Buscá en el sitio lo que necesitás.' }) {
    const [query, setQuery] = useState('')
    const [results, setResults] = useState(null)
    const [loading, setLoading] = useState(false)

    const handleSearch = useCallback(async (e) => {
        e?.preventDefault()
        if (!query.trim()) return

        setLoading(true)
        try {
            const res = await fetch(`/search-block?q=${encodeURIComponent(query)}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            })
            const data = await res.json()
            setResults(data)
        } catch (err) {
            console.error('Search error:', err)
        } finally {
            setLoading(false)
        }
    }, [query])

    return (
        <section className="py-12 md:py-16">
            <div className="container mx-auto px-4">
                <div className="mx-auto max-w-2xl text-center">
                    {title && (
                        <h2 className="text-3xl md:text-4xl font-bold text-gray-900 mb-4">{title}</h2>
                    )}
                    {description && (
                        <p className="text-lg text-gray-600 mb-8">{description}</p>
                    )}
                    <form onSubmit={handleSearch} className="flex gap-2">
                        <input
                            type="search"
                            value={query}
                            onChange={(e) => setQuery(e.target.value)}
                            placeholder="Buscar..."
                            className="flex-1 px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary/50 focus:border-primary"
                        />
                        <button
                            type="submit"
                            disabled={loading}
                            className="px-6 py-3 rounded-lg bg-primary text-white font-semibold hover:bg-primary-hover transition-colors disabled:opacity-50"
                        >
                            {loading ? 'Buscando...' : 'Buscar'}
                        </button>
                    </form>
                    {results && (
                        <div className="mt-8 text-left">
                            {results.results?.length > 0 ? (
                                <ul className="space-y-4">
                                    {results.results.map((item, i) => (
                                        <li key={i}>
                                            <a href={item.url} className="text-primary hover:underline font-medium">
                                                {item.title}
                                            </a>
                                        </li>
                                    ))}
                                </ul>
                            ) : (
                                <p className="text-gray-500 text-center">Sin resultados.</p>
                            )}
                        </div>
                    )}
                </div>
            </div>
        </section>
    )
}
