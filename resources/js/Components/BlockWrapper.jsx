export default function BlockWrapper({ block, children }) {
    const uid = block.data?.blockTitle
        ? block.data.blockTitle.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '')
        : `block-${Math.random().toString(36).slice(2, 9)}`

    const mb = block.data?.mb ?? ''
    const mdMb = block.data?.mdMb ?? ''
    const classes = block.data?.clases ?? []
    const styles = block.data?.styles ?? {}
    const stylesMd = block.data?.stylesMd ?? {}

    const allClasses = [mb, mdMb, ...classes].filter(Boolean).join(' ')

    const styleId = uid

    return (
        <div id={uid} className={`block block-${block.type} ${allClasses}`}>
            {Object.keys(styles).length > 0 && (
                <style>{`#${styleId} { ${Object.entries(styles).map(([k, v]) => `${k}: ${v}`).join('; ')} }`}</style>
            )}
            {Object.keys(stylesMd).length > 0 && (
                <style>{`@media (min-width: 768px) { #${styleId} { ${Object.entries(stylesMd).map(([k, v]) => `${k}: ${v}`).join('; ')} } }`}</style>
            )}
            {children}
        </div>
    )
}
