import SectionIntro from '@/Components/ClientCase/SectionIntro'

export default function ClientProcess({ eyebrow, title, body, nodes = [] }) {
    return (
        <section className="border-b border-white/20 py-12 md:py-16" data-case-block>
            <div className="container mx-auto grid gap-10 md:grid-cols-12 md:gap-8">
                <SectionIntro eyebrow={eyebrow} title={title} body={body} className="md:col-span-4" />
                <ol className="client-process__grid md:col-span-8" role="list">
                    {nodes.map((node, index) => (
                        <li key={`${node.label}-${index}`} className="client-process__node" data-process-node>
                            <span className="client-process__index" aria-hidden="true">{String(index + 1).padStart(2, '0')}</span>
                            <h3 className="text-lg font-bold md:text-xl">{node.label}</h3>
                            {node.detail && <p className="mt-2 text-sm leading-relaxed text-gray-2">{node.detail}</p>}
                        </li>
                    ))}
                </ol>
            </div>
        </section>
    )
}
