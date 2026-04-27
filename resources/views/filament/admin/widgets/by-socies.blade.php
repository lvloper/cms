<x-filament-widgets::widget>
    <x-filament::section>
        <div class="socies-dashboard-widget">
            <style>
                .socies-dashboard-widget {
                    display: grid;
                    justify-items: center;
                    gap: 1rem;
                    text-align: center;
                }

                .socies-dashboard-widget__logo {
                    display: flex;
                    gap: 0.375rem;
                    justify-content: center;
                }

                .socies-dashboard-widget__logo span {
                    display: grid;
                    place-items: center;
                    width: 2rem;
                    height: 2rem;
                    border-radius: 9999px;
                    color: white;
                    font-size: 0.875rem;
                    font-weight: 800;
                }

                .socies-dashboard-widget__eyebrow {
                    margin: 0.25rem 0 0;
                    color: var(--gray-400);
                    font-size: 0.625rem;
                    font-weight: 700;
                    letter-spacing: 0.55em;
                    text-transform: uppercase;
                }

                .socies-dashboard-widget__copy {
                    display: grid;
                    gap: 0.25rem;
                    max-width: 42rem;
                    margin: 0;
                    color: var(--gray-700);
                    font-size: 0.875rem;
                }

                .dark .socies-dashboard-widget__copy {
                    color: var(--gray-200);
                }

                .socies-dashboard-widget__actions {
                    display: flex;
                    flex-wrap: wrap;
                    gap: 0.75rem;
                    justify-content: center;
                }

                .socies-dashboard-widget__iframe {
                    width: 100%;
                    height: min(700px, 80vh);
                    border: 0;
                }
            </style>

            <div>
                <div class="socies-dashboard-widget__logo" aria-label="Socies">
                    <span style="background: #00f081;">S</span>
                    <span style="background: #1d71ff;">O</span>
                    <span style="background: #ffc700;">C</span>
                    <span style="background: #ff4d61;">I</span>
                    <span style="background: #951b81;">E</span>
                    <span style="background: #0dd6cc;">S</span>
                </div>

                <p class="socies-dashboard-widget__eyebrow">Factoría digital</p>
            </div>

            <div class="socies-dashboard-widget__copy">
                <p>Este proyecto fue desarrollado con cariño por socies.agency.</p>
                <p><strong>Somos tu aliado digital</strong>, no dudes en contar siempre con nosotros.</p>
            </div>

            <div class="socies-dashboard-widget__actions">
                <x-filament::modal width="5xl">
                    <x-slot name="trigger">
                        <x-filament::button color="gray" outlined>
                            Solicita una adaptación
                        </x-filament::button>
                    </x-slot>

                    <x-slot name="heading">
                        Solicita una adaptación
                    </x-slot>

                    <iframe
                        class="socies-dashboard-widget__iframe"
                        src="https://opnform.com/forms/solicitud-de-adaptaciones-o-desarrollo-jx82ic"
                        title="Solicitud de adaptaciones o desarrollo"
                    ></iframe>
                </x-filament::modal>

                <x-filament::button
                    color="gray"
                    href="https://www.instagram.com/socies.agency/"
                    outlined
                    tag="a"
                    target="_blank"
                >
                    Síguenos en Instagram
                </x-filament::button>

                <x-filament::modal width="5xl">
                    <x-slot name="trigger">
                        <x-filament::button color="gray" outlined>
                            Déjanos tu opinión
                        </x-filament::button>
                    </x-slot>

                    <x-slot name="heading">
                        Déjanos tu opinión
                    </x-slot>

                    <iframe
                        class="socies-dashboard-widget__iframe"
                        src="https://opnform.com/forms/encuesta-de-satisfaccion-zkjxoh"
                        title="Encuesta de satisfacción"
                    ></iframe>
                </x-filament::modal>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
