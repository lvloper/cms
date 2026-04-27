{{-- Paste Handler Alpine Attributes --}}
x-data="{
    // Focus paste input when clicking on editor area
    focusPasteInput() {
        const pasteInput = document.getElementById('blocks_pastable_{{ $statePath }}');
        if (pasteInput) {
            pasteInput.focus();
        }
    },
    // Paste functionality
    async handlePaste(event) {
        console.log('📋 === EVENTO PASTE DETECTADO ===');
        console.log('Evento completo:');
        console.dir(event);
        
        // Only handle paste when not in a text input/textarea, except for our paste input
        const isPasteInput = event.target.id === 'blocks_pastable_{{ $statePath }}';
        console.log('🎯 Target del evento:', event.target);
        console.log('🔍 Es input de paste especial:', isPasteInput);
        
        if ((event.target.tagName === 'INPUT' || event.target.tagName === 'TEXTAREA' || event.target.isContentEditable) && 
            !isPasteInput) {
            console.log('⏭️ Ignorando paste en campo de texto normal');
            return;
        }
        
        try {
            let clipboardData = '';
            
            console.log('📋 Intentando leer del clipboard...');
            console.log('- navigator.clipboard disponible:', !!navigator.clipboard);
            console.log('- navigator.clipboard.readText disponible:', !!(navigator.clipboard && navigator.clipboard.readText));
            console.log('- event.clipboardData disponible:', !!event.clipboardData);
            
            if (navigator.clipboard && navigator.clipboard.readText) {
                console.log('✅ Usando API moderna de clipboard');
                clipboardData = await navigator.clipboard.readText();
            } else if (event.clipboardData) {
                console.log('✅ Usando clipboardData del evento');
                clipboardData = event.clipboardData.getData('text');
            } else {
                console.warn('❌ No hay acceso al clipboard disponible');
                return;
            }
            
            console.log('📝 Datos del clipboard obtenidos:');
            console.log('Contenido:', clipboardData);
            console.dir(clipboardData);
            
            if (!clipboardData.trim()) {
                console.log('Clipboard is empty');
                return;
            }
            
            // Try to parse as JSON
            let blockData;
            try {
                blockData = JSON.parse(clipboardData);
            } catch (parseError) {
                console.log('Clipboard content is not valid JSON:', parseError);
                return;
            }
            
            // Validate block structure (Filament blocks have different structure)
            if (!blockData || typeof blockData !== 'object') {
                console.log('Invalid block structure - not an object:', blockData);
                return;
            }
            
            // Check if it's a valid Filament block structure
            // Filament blocks can have various structures, so we'll be more flexible
            const isValidBlock = (
                // Check for Filament builder block structure (has type and data)
                (blockData.hasOwnProperty('type') && blockData.hasOwnProperty('data')) ||
                // Check for raw block data structure (has common block properties)
                (blockData.hasOwnProperty('title') || 
                 blockData.hasOwnProperty('blockTitle') || 
                 blockData.hasOwnProperty('description') ||
                 blockData.hasOwnProperty('hidden') ||
                 blockData.hasOwnProperty('clases') ||
                 blockData.hasOwnProperty('mb') ||
                 blockData.hasOwnProperty('styles'))
            );
            
            if (!isValidBlock) {
                console.log('Invalid block structure - missing required properties:', blockData);
                return;
            }
            
            console.log('Valid block found, adding to blocks:', blockData);
            console.dir(blockData);
            
            // Validate that blockData has the correct structure
            if (!blockData.type || !blockData.data) {
                console.error('Block data missing type or data:', blockData);
                $wire.dispatch('notify', {
                    type: 'error',
                    title: 'Estructura inválida',
                    body: 'El bloque no tiene la estructura correcta (falta type o data)'
                });
                return;
            }
            
            console.log('🚀 Agregando bloque directamente...');
            console.log('- Tipo de bloque:', blockData.type);
            console.log('- Datos del bloque:');
            console.dir(blockData.data);
            
            // Get current state
            const currentState = $wire.get('{{ $statePath }}') || [];
            console.log('Estado actual:', currentState);
            
            // Generate a simple UUID (Livewire-compatible)
            const newUuid = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
                const r = Math.random() * 16 | 0;
                const v = c == 'x' ? r : (r & 0x3 | 0x8);
                return v.toString(16);
            });
            
            // Create new block with the standard Filament structure
            const newBlock = {
                type: blockData.type,
                data: blockData.data
            };
            
            // Add to state using UUID as key
            currentState[newUuid] = newBlock;
            
            console.log('Nuevo estado:', currentState);
            
            // Update the state directly
            $wire.set('{{ $statePath }}', currentState);
            
            console.log('✅ Bloque agregado exitosamente');
            
            // Trigger state updated callback
            setTimeout(() => {
                $wire.call('$refresh');
            }, 100);
            
            // Show success notification
            $wire.dispatch('notify', {
                type: 'success',
                title: 'Bloque pegado',
                body: 'El bloque se ha agregado correctamente al final de la lista'
            });
            
            event.preventDefault();
            
        } catch (error) {
            console.error('Error al pegar bloque:', error);
            $wire.dispatch('notify', {
                type: 'error',
                title: 'Error al pegar',
                body: 'No se pudo pegar el bloque. Verifica que sea un bloque válido.'
            });
        }
    }
}"
x-on:click="focusPasteInput()"
x-on:paste.window="handlePaste($event)"
