import { forwardRef } from 'react'

const ScrollCue = forwardRef(function ScrollCue(_, ref) {
    return (
        <div ref={ref} className="home-hero__scroll-cue" aria-hidden="true">
            <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                strokeWidth="2"
                strokeLinecap="round"
                strokeLinejoin="round"
                className="lucide lucide-chevron-down"
            >
                <path d="m6 9 6 6 6-6" />
            </svg>
        </div>
    )
})

export default ScrollCue
