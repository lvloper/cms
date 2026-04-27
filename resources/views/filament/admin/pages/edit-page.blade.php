<x-filament-panels::page
    @class([
        'fi-resource-edit-record-page',
        'fi-resource-' . str_replace('/', '-', $this->getResource()::getSlug()),
        'fi-resource-record-' . $record->getKey(),
    ])
>
    @isset($record->route)
        <div>
            <div class="socies-page-url-actions">
                <x-filament::button tooltip="Editar" x-on:click="focusSlug()" size="xs" color="gray" outlined>
                    {{ $record->url }}
                </x-filament::button>

                <x-filament::button tag="a" href="{{ $record->url }}" target="_blank" size="xs" tooltip="Ver página" color="gray" icon="heroicon-o-link" outlined />

                <x-filament::button tag="a" href="{{ $record->preview_url }}" target="_blank" tooltip="Vista previa" size="xs" color="gray" icon="heroicon-o-eye" outlined />
            </div>
        </div>

        <script>
            function focusSlug() {
                setTimeout(() => {
                    document.querySelector('[x-ref="tabsData"]')?.parentNode?.querySelector('[role="tab"]:nth-child(2)')?.click();
                }, 100);
                setTimeout(() => {
                    document.querySelector('[wire\\:model\\.live="data.route.slug"]')?.focus();
                }, 200);
            }
        </script>
    @endisset

    {{ $this->content }}

    <style>
        .socies-page-url-actions {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: -1.5rem;
        }

        .fi-sc-actions,
        .fi-form-actions {
            position: sticky;
            bottom: 0;
            z-index: 20;
            margin: -5px;
            padding: .5rem;
            width: 100%;
            background: var(--gray-50);
        }

        .dark .fi-sc-actions,
        .dark .fi-form-actions {
            background: #09090b;
        }
    </style>
</x-filament-panels::page>
