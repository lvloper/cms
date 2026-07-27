export default function Image({ image, imageMobile, src, background, className, imageClass, fit = 'contain', alt, caption, default: defaultSrc }) {
    const resolveImages = (img) => {
        if (!img) return []
        const arr = Array.isArray(img) ? img : [img]
        return arr
    }

    const srcArray = src ? [src] : null
    const desktopImages = srcArray ?? resolveImages(image)
    const mobileImages = srcArray ? srcArray : resolveImages(imageMobile)

    const hasMobile = mobileImages.length > 0 && !src

    const renderBg = (imgs, isMobile) =>
        imgs.map((img, i) => (
            <div
                key={`${isMobile ? 'm' : 'd'}-${i}`}
                className={`${className ?? ''} ${hasMobile ? (isMobile ? 'md:hidden' : 'hidden md:block') : ''}`}
                style={{
                    backgroundImage: `url('${img}')`,
                    backgroundSize: 'cover',
                    backgroundPosition: 'center',
                    backgroundRepeat: 'no-repeat',
                    ...(imageClass ? parseImageClass(imageClass) : {}),
                }}
                role="img"
                aria-label={alt ?? caption ?? ''}
            >
                {caption && (
                    <div className="px-6 py-4 bg-gray-200">{caption}</div>
                )}
            </div>
        ))

    const renderImg = (imgs, isMobile) =>
        imgs.map((img, i) => (
            <figure key={`${isMobile ? 'm' : 'd'}-${i}`} className={`${className ?? ''} ${hasMobile ? (isMobile ? 'md:hidden' : 'hidden md:block') : ''}`}>
                <img
                    src={img}
                    alt={alt ?? caption ?? ''}
                    className={imageClass ?? `object-${fit} w-full h-full`}
                />
                {caption && (
                    <figcaption className="px-6 py-4 bg-gray-200">{caption}</figcaption>
                )}
            </figure>
        ))

    if (background) {
        return (
            <>
                {hasMobile && renderBg(mobileImages, true)}
                {desktopImages.length > 0 && renderBg(desktopImages, false)}
            </>
        )
    }

    return (
        <>
            {hasMobile && renderImg(mobileImages, true)}
            {desktopImages.length > 0 && renderImg(desktopImages, false)}
        </>
    )
}

function parseImageClass(styleString) {
    const styles = {}
    styleString.split(';').forEach(rule => {
        const [prop, val] = rule.split(':').map(s => s.trim())
        if (prop && val) {
            const camelProp = prop.replace(/-([a-z])/g, (_, c) => c.toUpperCase())
            styles[camelProp] = val
        }
    })
    return styles
}
