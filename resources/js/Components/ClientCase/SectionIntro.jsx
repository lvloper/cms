export default function SectionIntro({ eyebrow, title, body, className = '' }) {
    return (
        <div className={className} data-client-reveal>
            {eyebrow && (
                <p className="mb-4 flex items-center gap-3 text-xs font-bold tracking-widest text-socies-green">
                    <span className="h-px w-8 bg-current" aria-hidden="true" />
                    {eyebrow}
                </p>
            )}
            {title && <h2 className="max-w-4xl text-3xl font-bold leading-tight md:text-5xl">{title}</h2>}
            {body && <p className="mt-5 max-w-2xl text-base leading-relaxed text-gray-2 md:text-lg">{body}</p>}
        </div>
    )
}
