export default function ClientOrnaments() {
    return (
        <div className="client-case-ornaments" aria-hidden="true">
            <svg className="client-case-ornaments__top" viewBox="0 0 260 220" fill="none">
                <circle cx="190" cy="28" r="42" className="fill-socies-green" />
                <path d="M48 4a44 44 0 0 1 44 44H48V4Z" className="fill-socies-coral" />
                <path d="M48 92a44 44 0 0 1-44-44h44v44Z" className="fill-socies-yellow" />
                <path d="m155 82 34 58h-68l34-58Z" className="fill-white" />
                <path d="M220 112v54M193 139h54" className="stroke-socies-green" strokeWidth="12" />
                <path d="M76 174h78m0 0-24-24m24 24-24 24" className="stroke-socies-yellow" strokeWidth="10" />
            </svg>

            <svg className="client-case-ornaments__bottom" viewBox="0 0 260 220" fill="none">
                <circle cx="48" cy="174" r="38" className="fill-socies-coral" />
                <path d="M126 126a46 46 0 0 1 46 46h-46v-46Z" className="fill-socies-yellow" />
                <path d="M126 218a46 46 0 0 1-46-46h46v46Z" className="fill-socies-green" />
                <path d="m76 30 30 52H46l30-52Z" className="fill-white" />
                <path d="M180 30v52M154 56h52" className="stroke-socies-coral" strokeWidth="12" />
                <path d="M154 112h72m0 0-22-22m22 22-22 22" className="stroke-socies-green" strokeWidth="10" />
            </svg>
        </div>
    )
}
