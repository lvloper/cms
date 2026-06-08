{{-- Paste Handler Alpine Attributes --}}
x-data="{
    pasting: false,
    // Focus paste input before Ctrl+V so the paste event fires on it
    preFocusForPaste(event) {
        if ((event.ctrlKey || event.metaKey) && event.key === 'v') {
            if (event.target.tagName === 'INPUT' || event.target.tagName === 'TEXTAREA' || event.target.isContentEditable) {
                return;
            }
            const pasteInput = document.getElementById('blocks_pastable_{{ $statePath }}');
            if (pasteInput) pasteInput.focus();
        }
    },
    handleSaveShortcut(event) {
        if ((event.ctrlKey || event.metaKey) && event.key === 's') {
            event.preventDefault();
            const saveBtn = document.querySelector('[wire\\:click=&quot;save&quot;]');
            if (saveBtn) {
                saveBtn.click();
                return;
            }
            const form = document.querySelector('form[wire\\:submit]');
            if (form) form.requestSubmit();
        }
    },
    // Paste functionality
    async handlePaste(event) {
        this.pasting = true;

        // Only handle paste when not in a text input/textarea, except for our paste input
        const isPasteInput = event.target.id === 'blocks_pastable_{{ $statePath }}';

        if ((event.target.tagName === 'INPUT' || event.target.tagName === 'TEXTAREA' || event.target.isContentEditable) && 
            !isPasteInput) {
            return;
        }

        try {
            let clipboardData = '';

            if (navigator.clipboard && navigator.clipboard.readText) {
                clipboardData = await navigator.clipboard.readText();
            } else if (event.clipboardData) {
                clipboardData = event.clipboardData.getData('text');
            } else {
                return;
            }

            if (!clipboardData.trim()) {
                return;
            }

            // Try to parse as JSON
            let blockData;
            try {
                blockData = JSON.parse(clipboardData);
            } catch (parseError) {
                return;
            }

            // Validate block structure
            if (!blockData || typeof blockData !== 'object') {
                return;
            }

            const isValidBlock = (
                (blockData.hasOwnProperty('type') && blockData.hasOwnProperty('data')) ||
                (blockData.hasOwnProperty('title') || 
                 blockData.hasOwnProperty('blockTitle') || 
                 blockData.hasOwnProperty('description') ||
                 blockData.hasOwnProperty('hidden') ||
                 blockData.hasOwnProperty('clases') ||
                 blockData.hasOwnProperty('mb') ||
                 blockData.hasOwnProperty('styles'))
            );

            if (!isValidBlock) {
                return;
            }

            if (!blockData.type || !blockData.data) {
                $wire.dispatch('notify', {
                    type: 'error',
                    title: 'Estructura inválida',
                    body: 'El bloque no tiene la estructura correcta (falta type o data)'
                });
                return;
            }

            // Get current state
            const currentState = $wire.get('{{ $statePath }}') || [];

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

            // Find nearest visible block to scroll position
            const nearestUuid = (() => {
                const items = document.querySelectorAll('.fi-visual-editor__item[x-sortable-item]');
                if (!items.length) return null;

                const viewportCenter = window.innerHeight / 2;
                let nearest = null;
                let nearestDist = Infinity;

                items.forEach(item => {
                    const rect = item.getBoundingClientRect();
                    const center = rect.top + rect.height / 2;
                    const dist = Math.abs(center - viewportCenter);
                    if (dist < nearestDist) {
                        nearestDist = dist;
                        nearest = item.getAttribute('x-sortable-item');
                    }
                });

                return nearest;
            })();

            // Insert after the nearest block, or at end if none found
            const newState = {};
            let inserted = false;
            for (const [key, value] of Object.entries(currentState)) {
                newState[key] = value;
                if (!inserted && key === nearestUuid) {
                    newState[newUuid] = newBlock;
                    inserted = true;
                }
            }
            if (!inserted) {
                newState[newUuid] = newBlock;
            }

            // Update the state with the reordered object
            $wire.set('{{ $statePath }}', newState);

            // Trigger state updated callback
            setTimeout(() => {
                $wire.call('$refresh');
            }, 100);

            // Show success notification
            $wire.dispatch('notify', {
                type: 'success',
                title: 'Bloque pegado',
                body: nearestUuid ? 'El bloque se ha insertado cerca de la posicion actual' : 'El bloque se ha agregado al final de la lista'
            });

            event.preventDefault();

        } catch (error) {
            $wire.dispatch('notify', {
                type: 'error',
                title: 'Error al pegar',
                body: 'No se pudo pegar el bloque. Verifica que sea un bloque válido.'
            });
        } finally {
            this.pasting = false;
        }
    }
}"
x-on:keydown.window="handleSaveShortcut($event); preFocusForPaste($event)"
x-on:paste.window="handlePaste($event)"
