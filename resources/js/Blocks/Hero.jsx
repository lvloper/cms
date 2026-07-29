import { useEffect, useRef, useState, useCallback } from 'react'

const SOCIES = [
    { letter: 'S', color: 'hero-letter--s1' },
    { letter: 'O', color: 'hero-letter--o' },
    { letter: 'C', color: 'hero-letter--c' },
    { letter: 'I', color: 'hero-letter--i' },
    { letter: 'E', color: 'hero-letter--e' },
    { letter: 'S', color: 'hero-letter--s2' },
]

export default function Hero({ title, subtitle, buttonText, buttonLink }) {
    const [scene, setScene] = useState(0)
    const ref = useRef(null)
    const timerRef = useRef([])

    const advance = useCallback((next) => {
        timerRef.current.push(setTimeout(() => setScene(next), 0))
    }, [])

    useEffect(() => {
        const SPEED = 1
        const S1 = 2000 * SPEED
        const S2 = 450 * SPEED
        const S3 = 300 * SPEED
        const S4 = 900 * SPEED

        setScene(1)

        document.documentElement.classList.add('hero-scroll-lock')

        timerRef.current.push(setTimeout(() => setScene(2), S1))
        timerRef.current.push(setTimeout(() => setScene(3), S1 + S2))
        timerRef.current.push(setTimeout(() => setScene(4), S1 + S2 + S3))
        timerRef.current.push(setTimeout(() => setScene(5), S1 + S2 + S3 + S4))

        return () => {
            timerRef.current.forEach(clearTimeout)
            document.documentElement.classList.remove('hero-scroll-lock')
        }
    }, [])

    useEffect(() => {
        if (scene === 5) {
            document.documentElement.classList.remove('hero-scroll-lock')
        }
    }, [scene])

    const sceneClass = scene === 0 ? ''
        : scene === 5 ? 'is-done'
        : `scene-${scene}`

    const isPlaying = scene > 0 && scene < 5
    const showContent = scene >= 4

    const chars = title ? title.split('').map((ch, i) => {
        if (ch === ' ') return { type: 'space', key: i }
        if (ch === '→') return { type: 'mark', mark: 'arrow', key: i }
        if (ch === '.') return { type: 'mark', mark: 'dot', key: i }
        return { type: 'char', char: ch, key: i }
    }) : []

    return (
        <div ref={ref} className={`hero-inner ${sceneClass} ${isPlaying ? 'is-playing' : ''}`}>
            <div className="hero-overlay" />

            <div className="hero-stage">
                <div className="hero-line" />
                <div className="hero-logo">
                    {SOCIES.map(({ letter, color }, index) => (
                        <div key={`${letter}-${index}`} className={`hero-letter ${color}`}>
                            <svg viewBox="0 0 100 100" fill="none">
                                <text x="50" y="68" textAnchor="middle" fontSize="64" fontWeight="800" fill="currentColor">
                                    {letter}
                                </text>
                            </svg>
                        </div>
                    ))}
                </div>
            </div>

            <div className="hero-content">
                {showContent && title && (
                    <h1 className="hero-title">
                        {chars.map((item) => {
                            if (item.type === 'space') {
                                return <span key={item.key} className="hero-title__space" />
                            }
                            if (item.type === 'mark') {
                                return (
                                    <span key={item.key}
                                        className={`hero-title__mark hero-title__mark--${item.mark}`}
                                    >
                                        {item.mark === 'arrow' ? (
                                            <svg width="100%" height="100%" viewBox="0 0 40 40" fill="none">
                                                <path d="M16 10l10 10-10 10" stroke="currentColor" strokeWidth="4" strokeLinecap="round" strokeLinejoin="round" />
                                            </svg>
                                        ) : null}
                                    </span>
                                )
                            }
                            return (
                                <span key={item.key} className="hero-title__char">
                                    <span>{item.char}</span>
                                </span>
                            )
                        })}
                    </h1>
                )}

                {showContent && subtitle && (
                    <p className="hero-subtitle">{subtitle}</p>
                )}

                {showContent && buttonText && buttonLink && (
                    <div className="hero-actions">
                        <a href={buttonLink} className="hero-btn">
                            {buttonText}
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                <path d="M6 4l4 4-4 4" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
                            </svg>
                        </a>
                    </div>
                )}
            </div>
        </div>
    )
}
