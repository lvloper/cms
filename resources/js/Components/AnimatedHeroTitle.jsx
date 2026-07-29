import { forwardRef } from 'react'

function ArrowDownRight() {
    return (
        <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            strokeWidth="2"
            strokeLinecap="round"
            strokeLinejoin="round"
            className="lucide lucide-arrow-down-right"
            aria-hidden="true"
        >
            <path d="m7 7 10 10" />
            <path d="M17 7v10H7" />
        </svg>
    )
}

const AnimatedHeroTitle = forwardRef(function AnimatedHeroTitle({ title, rotatingWords = [] }, ref) {
    const words = title.trim().split(/\s+/)
    const rotatingWord = rotatingWords[0]?.toLowerCase()

    return (
        <h1
            ref={ref}
            id="home-hero-title"
            className="home-hero__title"
            aria-label={title}
        >
            <span className="home-hero__title-visual" aria-hidden="true">
                <span className="home-hero__arrow" data-title-mark>
                    <ArrowDownRight />
                </span>

                {words.map((word, wordIndex) => (
                    <span
                        key={`${word}-${wordIndex}`}
                        className={`home-hero__word${word.toLowerCase() === rotatingWord ? ' home-hero__word--green' : ''}`}
                        data-title-word
                    >
                        {word.toLowerCase() === rotatingWord ? (
                            <span className="home-hero__rotating-word" data-rotating-word>
                                {rotatingWords.map((option) => (
                                    <span
                                        key={option}
                                        className="home-hero__rotating-option"
                                        data-rotating-option
                                    >
                                        {Array.from(option).map((character, characterIndex) => (
                                            <span
                                                key={`${character}-${characterIndex}`}
                                                className="home-hero__character-mask"
                                            >
                                                <span className="home-hero__character" data-title-character>
                                                    {character}
                                                </span>
                                            </span>
                                        ))}
                                    </span>
                                ))}
                            </span>
                        ) : (
                            Array.from(word).map((character, characterIndex) => (
                                <span
                                    key={`${character}-${characterIndex}`}
                                    className="home-hero__character-mask"
                                >
                                    <span className="home-hero__character" data-title-character>
                                        {character}
                                    </span>
                                </span>
                            ))
                        )}
                        {wordIndex < words.length - 1 && (
                            <span className="home-hero__word-space">&nbsp;</span>
                        )}
                    </span>
                ))}

                <span className="home-hero__dot" data-title-mark />
            </span>
        </h1>
    )
})

export default AnimatedHeroTitle
