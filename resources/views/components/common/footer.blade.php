<footer class="border-t border-white/20 bg-black py-12 text-white md:py-16">
    <div class="container mx-auto">
        <div class="grid items-center gap-8 md:grid-cols-12 md:gap-6">
            <a href="/" class="block w-36 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-focus md:col-span-4 md:w-40" aria-label="Socies — Inicio">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 601 101" aria-hidden="true" focusable="false" class="block w-full">
                    <g fill="var(--color-white)">
                        <circle cx="50" cy="50" r="50" />
                        <circle cx="150" cy="50" r="50" />
                        <circle cx="250" cy="50" r="50" />
                        <circle cx="350" cy="50" r="50" />
                        <circle cx="450" cy="50" r="50" />
                        <circle cx="550" cy="50" r="50" />
                    </g>
                    <g fill="var(--color-black)" font-size="62" font-weight="800" text-anchor="middle">
                        <text x="50" y="71">S</text>
                        <text x="150" y="71">O</text>
                        <text x="250" y="71">C</text>
                        <text x="350" y="71">I</text>
                        <text x="450" y="71">E</text>
                        <text x="550" y="71">S</text>
                    </g>
                </svg>
            </a>

            <nav aria-label="Navegación del pie" class="md:col-span-4">
                <ul class="flex flex-wrap gap-x-8 gap-y-3 text-sm font-medium">
                    <li><a href="/#clientes" class="transition-colors hover:text-socies-green focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-focus">Clientes</a></li>
                    <li><a href="/#hablemos" class="transition-colors hover:text-socies-green focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-focus">Qué hacemos</a></li>
                </ul>
            </nav>

            <nav aria-label="Redes sociales" class="md:col-span-4 md:justify-self-end">
                <ul class="flex flex-wrap gap-x-8 gap-y-3 text-sm font-medium">
                    <li><a href="{{ config('social-media.networks.linkedin.url') }}" target="_blank" rel="noreferrer" class="transition-colors hover:text-socies-green focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-focus">LinkedIn</a></li>
                    <li><a href="{{ config('social-media.networks.instagram.url') }}" target="_blank" rel="noreferrer" class="transition-colors hover:text-socies-green focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-focus">Instagram</a></li>
                </ul>
            </nav>
        </div>

        <div class="mt-12 border-t border-white/20 pt-4 text-xs text-gray-2">
            <p>© {{ now()->year }} Socies</p>
        </div>
    </div>
</footer>
