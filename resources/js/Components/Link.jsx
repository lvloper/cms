export default function Link({ link, className, children, hideIfNull }) {
    if (!link || !link.url || link.url === '#') {
        if (hideIfNull) return null
        return <div className={className}>{children}</div>
    }

    if (link.is_modal) {
        return (
            <a
                href={link.url}
                className={className}
                data-modal-trigger={link.route_id}
            >
                {children ?? link.label}
            </a>
        )
    }

    return (
        <a
            href={link.url}
            className={className}
            target={link.new_window ? '_blank' : undefined}
            rel={link.new_window ? 'noopener noreferrer' : undefined}
            download={link.is_file ? link.download_name || true : undefined}
        >
            {children ?? link.label}
        </a>
    )
}
