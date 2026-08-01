import { useState } from 'react'
import { ClientStoryCard } from '@/Components/ClientStoryPopup'

function ClientStoryGridItem({ client, index }) {
    const [isActive, setIsActive] = useState(false)

    return (
        <li
            className="client-stories__item"
            data-client-story-position={(index % 3) + 1}
            onMouseEnter={() => setIsActive(true)}
            onMouseLeave={() => setIsActive(false)}
            onFocus={() => setIsActive(true)}
            onBlur={(event) => {
                if (!event.currentTarget.contains(event.relatedTarget)) {
                    setIsActive(false)
                }
            }}
        >
            <a
                className="client-stories__link"
                href={client.url}
                aria-label={`Ver historia de ${client.title || client.alt}`}
            >
                <ClientStoryCard
                    client={client}
                    isActive={isActive}
                    showLink={false}
                />
            </a>
        </li>
    )
}

export default function ClientStoriesGrid({ clients = [] }) {
    if (clients.length === 0) {
        return null
    }

    return (
        <section
            id="clientes"
            aria-labelledby="clients-title"
            className="client-stories"
        >
            <div className="client-stories__intro container mx-auto">
                <p className="client-stories__eyebrow">Clientes</p>
                <h2 id="clients-title" className="client-stories__title">
                    Historias que se ponen en movimiento.
                </h2>
                <p className="client-stories__hint">
                    Recorré cada historia al pasar el cursor.
                </p>
            </div>

            <ul className="client-stories__grid container mx-auto" role="list">
                {clients.map((client, index) => (
                    <ClientStoryGridItem
                        key={client.id}
                        client={client}
                        index={index}
                    />
                ))}
            </ul>
        </section>
    )
}
