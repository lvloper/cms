import { forwardRef } from 'react'

const SociesLogo = forwardRef(function SociesLogo(
    { className = '', variant = 'brand', ...props },
    ref,
) {
    return (
        <svg
            ref={ref}
            className={`socies-logo socies-logo--${variant} ${className}`.trim()}
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 601 101"
            aria-hidden="true"
            focusable="false"
            {...props}
        >
            <g className="socies-logo__track" data-logo-track>
                <g className="socies-logo__letter" data-logo-letter>
                    <circle className="socies-logo__circle socies-logo__circle--green" data-logo-circle cx="50" cy="50" r="50" />
                    <g transform="translate(33.759 30.756)">
                        <path
                            className="socies-logo__glyph"
                            data-logo-glyph
                            d="M273.759,1424.029l5.741-6.858a19.168,19.168,0,0,0,12.068,4.465c2.765,0,4.253-.956,4.253-2.551v-.107c0-1.541-1.223-2.392-6.273-3.561-7.921-1.808-14.035-4.041-14.035-11.7v-.106c0-6.911,5.476-11.908,14.407-11.908,6.326,0,11.27,1.7,15.311,4.944l-5.157,7.283a18.659,18.659,0,0,0-10.42-3.668c-2.5,0-3.721,1.063-3.721,2.392v.106c0,1.7,1.276,2.446,6.432,3.615,8.56,1.861,13.876,4.625,13.876,11.589v.107c0,7.6-6.008,12.121-15.045,12.121A26,26,0,0,1,273.759,1424.029Z"
                            transform="translate(-273.759 -1391.707)"
                        />
                    </g>
                </g>

                <g className="socies-logo__letter" data-logo-letter>
                    <circle className="socies-logo__circle socies-logo__circle--blue" data-logo-circle cx="50" cy="50" r="50" transform="translate(100)" />
                    <g transform="translate(129.958 30.649)">
                        <path
                            className="socies-logo__glyph"
                            data-logo-glyph
                            d="M369.958,1411.057v-.106c0-10.685,8.612-19.351,20.1-19.351s19.989,8.559,19.989,19.245v.106c0,10.685-8.612,19.351-20.1,19.351S369.958,1421.743,369.958,1411.057Zm29.558,0v-.106c0-5.369-3.881-10.047-9.569-10.047-5.635,0-9.41,4.571-9.41,9.941v.106c0,5.369,3.881,10.047,9.516,10.047C395.741,1421,399.516,1416.427,399.516,1411.057Z"
                            transform="translate(-369.958 -1391.6)"
                        />
                    </g>
                </g>

                <g className="socies-logo__letter" data-logo-letter>
                    <circle className="socies-logo__circle socies-logo__circle--yellow" data-logo-circle cx="50" cy="50" r="50" transform="translate(200)" />
                    <g transform="translate(229.914 30.649)">
                        <path
                            className="socies-logo__glyph"
                            data-logo-glyph
                            d="M469.914,1411.057v-.106c0-10.845,8.347-19.351,19.617-19.351,7.6,0,12.493,3.19,15.789,7.762l-7.762,6.007c-2.126-2.658-4.572-4.359-8.133-4.359-5.21,0-8.879,4.412-8.879,9.835v.106c0,5.582,3.669,9.941,8.879,9.941,3.88,0,6.166-1.807,8.4-4.519l7.762,5.529c-3.509,4.838-8.24,8.4-16.48,8.4A18.9,18.9,0,0,1,469.914,1411.057Z"
                            transform="translate(-469.914 -1391.6)"
                        />
                    </g>
                </g>

                <g className="socies-logo__letter" data-logo-letter>
                    <circle className="socies-logo__circle socies-logo__circle--coral" data-logo-circle cx="50" cy="50" r="50" transform="translate(300)" />
                    <g transform="translate(344.817 31.393)">
                        <path
                            className="socies-logo__glyph"
                            data-logo-glyph
                            d="M584.817,1392.344h10.366v37.214H584.817Z"
                            transform="translate(-584.817 -1392.344)"
                        />
                    </g>
                </g>

                <g className="socies-logo__letter" data-logo-letter>
                    <circle className="socies-logo__circle socies-logo__circle--violet" data-logo-circle cx="50" cy="50" r="50" transform="translate(400)" />
                    <g transform="translate(434.902 31.393)">
                        <path
                            className="socies-logo__glyph"
                            data-logo-glyph
                            d="M674.9,1392.344h29.93v8.772H685.109v5.635h17.862v8.134H685.109v5.9H705.1v8.772H674.9Z"
                            transform="translate(-674.902 -1392.344)"
                        />
                    </g>
                </g>

                <g className="socies-logo__letter" data-logo-letter>
                    <circle className="socies-logo__circle socies-logo__circle--aqua" data-logo-circle cx="50" cy="50" r="50" transform="translate(500)" />
                    <g transform="translate(533.759 30.756)">
                        <path
                            className="socies-logo__glyph"
                            data-logo-glyph
                            d="M773.759,1424.029l5.741-6.858a19.168,19.168,0,0,0,12.068,4.465c2.765,0,4.253-.956,4.253-2.551v-.107c0-1.541-1.223-2.392-6.273-3.561-7.921-1.808-14.035-4.041-14.035-11.7v-.106c0-6.911,5.476-11.908,14.407-11.908,6.326,0,11.27,1.7,15.311,4.944l-5.157,7.283a18.658,18.658,0,0,0-10.42-3.668c-2.5,0-3.721,1.063-3.721,2.392v.106c0,1.7,1.276,2.446,6.432,3.615,8.56,1.861,13.876,4.625,13.876,11.589v.107c0,7.6-6.008,12.121-15.045,12.121A26,26,0,0,1,773.759,1424.029Z"
                            transform="translate(-773.759 -1391.707)"
                        />
                    </g>
                </g>
            </g>
        </svg>
    )
})

export default SociesLogo
