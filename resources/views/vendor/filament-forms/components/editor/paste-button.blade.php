{{-- Paste Button Component --}}
@props(['statePath'])

@php
$pasteScript = <<<'JS'
async function() {
    try {
        console.log('Paste button clicked');
        
        if (!navigator.clipboard || !navigator.clipboard.readText) {
            $wire.dispatch('notify', {
                type: 'error',
                title: 'Error',
                body: 'Tu navegador no soporta acceso al portapapeles'
            });
            return;
        }
        
        const clipboardData = await navigator.clipboard.readText();
        console.log('Clipboard data:', clipboardData);
        
        if (!clipboardData.trim()) {
            $wire.dispatch('notify', {
                type: 'warning',
                title: 'Portapapeles vacío',
                body: 'No hay contenido para pegar'
            });
            return;
        }
        
        let blockData;
        try {
            blockData = JSON.parse(clipboardData);
        } catch (parseError) {
            $wire.dispatch('notify', {
                type: 'error',
                title: 'Formato inválido',
                body: 'El contenido del portapapeles no es un bloque válido'
            });
            return;
        }
        
        if (!blockData || typeof blockData !== 'object') {
            $wire.dispatch('notify', {
                type: 'error',
                title: 'Estructura inválida',
                body: 'El contenido no es un objeto válido'
            });
            return;
        }
        
        const isValidBlock = (blockData.hasOwnProperty('type') && blockData.hasOwnProperty('data')) ||
                           (blockData.hasOwnProperty('title') || blockData.hasOwnProperty('blockTitle') || 
                            blockData.hasOwnProperty('description') || blockData.hasOwnProperty('hidden') ||
                            blockData.hasOwnProperty('clases') || blockData.hasOwnProperty('mb') || 
                            blockData.hasOwnProperty('styles'));
        
        if (!isValidBlock) {
            $wire.dispatch('notify', {
                type: 'error',
                title: 'Estructura inválida',
                body: 'El contenido no parece ser un bloque válido'
            });
            return;
        }
        
        if (!blockData.type || !blockData.data) {
            $wire.dispatch('notify', {
                type: 'error',
                title: 'Estructura inválida',
                body: 'El bloque no tiene la estructura correcta'
            });
            return;
        }
        
        console.log('Adding block:', blockData);
        
        // Get current state
        const currentState = $wire.get('STATE_PATH') || [];
        
        // Generate UUID
        const newUuid = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
            const r = Math.random() * 16 | 0;
            const v = c == 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
        
        // Create new block
        const newBlock = {
            type: blockData.type,
            data: blockData.data
        };
        
        // Add to state
        currentState[newUuid] = newBlock;
        
        // Update state
        $wire.set('STATE_PATH', currentState);
        
        console.log('Bloque agregado');
        
        // Refresh component
        setTimeout(() => {
            $wire.call('$refresh');
        }, 100);
        
        $wire.dispatch('notify', {
            type: 'success',
            title: 'Bloque pegado',
            body: 'El bloque se ha agregado correctamente'
        });
        
    } catch (error) {
        console.error('Error al pegar bloque:', error);
        $wire.dispatch('notify', {
            type: 'error',
            title: 'Error inesperado',
            body: 'Ocurrió un error al pegar el bloque'
        });
    }
}
JS;
$pasteScript = str_replace('STATE_PATH', $statePath, $pasteScript);
@endphp

<x-filament::icon-button
    color="gray"
    icon="heroicon-o-clipboard-document-list"
    size="sm"
    tooltip="Pegar bloque desde portapapeles"
    x-on:click="{{ $pasteScript }}"
>
    Pegar
</x-filament::icon-button>
