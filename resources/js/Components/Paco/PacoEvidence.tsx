import type { PacoEvidenceItem } from './types'

export function PacoEvidence({ items }: { items: PacoEvidenceItem[] }) {
    if (items.length === 0) return null

    return (
        <ul className="paco-evidence" aria-label="Experiencia relacionada de Socies">
            {items.map((item) => (
                <li key={item.item_id}>
                    {item.entity_type === 'testimonial'
                        ? <TestimonialEvidence item={item} />
                        : <WorkEvidence item={item} />}
                </li>
            ))}
        </ul>
    )
}

function WorkEvidence({ item }: { item: PacoEvidenceItem }) {
    return (
        <article className="paco-evidence-card">
            {item.image_url && (
                <img
                    className="paco-evidence-card__image"
                    src={item.image_url}
                    alt=""
                    loading="lazy"
                />
            )}
            <div className="paco-evidence-card__body">
                <span className="paco-evidence-card__client">{item.client_name}</span>
                <h3>{item.title || 'Trabajo relacionado'}</h3>
                <dl>
                    {item.problem && <EvidenceDetail label="El desafío" value={item.problem} />}
                    {item.solution && <EvidenceDetail label="Qué hicimos" value={item.solution} />}
                    {item.result && <EvidenceDetail label="Resultado documentado" value={item.result} />}
                </dl>
                {item.url && <EvidenceLink url={item.url} label="Ver trabajo" />}
            </div>
        </article>
    )
}

function TestimonialEvidence({ item }: { item: PacoEvidenceItem }) {
    return (
        <figure className="paco-evidence-card paco-evidence-card--testimonial">
            <div className="paco-evidence-card__body">
                <div className="paco-evidence-card__testimonial-head">
                    <div className="paco-evidence-card__person">
                        {item.image_url && (
                            <img
                                className="paco-evidence-card__avatar"
                                src={item.image_url}
                                alt={item.author ? `Foto de ${item.author}` : ''}
                                loading="lazy"
                            />
                        )}
                        {(item.author || item.role) && (
                            <figcaption>
                                {item.author && <strong>{item.author}</strong>}
                                {item.role && <span>{item.role}</span>}
                            </figcaption>
                        )}
                    </div>
                    {item.client_logo_url && (
                        <img
                            className="paco-evidence-card__logo"
                            src={item.client_logo_url}
                            alt={`Logo de ${item.client_name}`}
                            loading="lazy"
                        />
                    )}
                </div>
                <blockquote>“{item.quote}”</blockquote>
                {item.url && <EvidenceLink url={item.url} label="Ver testimonio completo" />}
            </div>
        </figure>
    )
}

function EvidenceDetail({ label, value }: { label: string; value: string }) {
    return (
        <div>
            <dt>{label}</dt>
            <dd>{value}</dd>
        </div>
    )
}

function EvidenceLink({ url, label }: { url: string; label: string }) {
    return (
        <a href={url} target="_blank" rel="noreferrer">
            {label}<span className="sr-only"> (se abre en una pestaña nueva)</span>
        </a>
    )
}
