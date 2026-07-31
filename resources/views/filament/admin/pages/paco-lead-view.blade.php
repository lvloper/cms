<x-filament-panels::page>
    <div
        class="paco-admin-preview"
        data-paco-read-only
        data-paco-state="{{ json_encode($conversationState, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) }}"
        aria-label="Vista de solo lectura de la conversación de {{ $lead->name ?: 'la consulta' }}"
    ></div>

    @vite(['resources/css/app.css', 'resources/js/admin-paco-preview.jsx'])
</x-filament-panels::page>
