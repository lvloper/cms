import { TiptapContent } from '@/Lib/tiptap'
import Link from '@/Components/Link'

export default function CardsBlock({ title, description, items = [] }) {
    return (
        <section className="py-12 md:py-16">
            <div className="container mx-auto px-4">
                {title && (
                    <h2 className="text-3xl md:text-4xl font-bold text-gray-900 mb-4 text-center">{title}</h2>
                )}
                {description && (
                    <TiptapContent
                        content={description}
                        className="prose prose-lg max-w-3xl mx-auto text-gray-600 text-center mb-10"
                    />
                )}
                {items.length > 0 && (
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        {items.map((item, i) => (
                            <div
                                key={i}
                                className="group rounded-xl border border-gray-200 bg-white overflow-hidden hover:shadow-lg transition-shadow"
                            >
                                {item.image && (
                                    <div className="aspect-[4/3] overflow-hidden bg-gray-100">
                                        <img
                                            src={item.image}
                                            alt={item.title ?? ''}
                                            className="h-full w-full object-cover group-hover:scale-105 transition-transform duration-300"
                                        />
                                    </div>
                                )}
                                <div className="p-5">
                                    {item.title && (
                                        <h3 className="text-lg font-semibold text-gray-900 mb-2">{item.title}</h3>
                                    )}
                                    {item.description && (
                                        <p className="text-sm text-gray-600">{item.description}</p>
                                    )}
                                    {item.route?.url && item.route.url !== '#' && (
                                        <Link
                                            link={item.route}
                                            className="inline-flex items-center mt-3 text-sm font-medium text-primary hover:underline"
                                        />
                                    )}
                                </div>
                            </div>
                        ))}
                    </div>
                )}
            </div>
        </section>
    )
}
