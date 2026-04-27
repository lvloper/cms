{{-- Copy Block Button Component --}}
@props(['item'])

<li x-on:click.stop
    x-data="{
        async copyBlock() {
            console.log('🖱️ CLICK EN BOTÓN DE COPIAR DETECTADO');
            console.log('=== INICIANDO COPIA DE BLOQUE ===');
            
            try {
                const blockType = '{{ $item->getParentComponent()->getName() }}';
                const blockData = @js($item->getRawState());
                
                console.log('🔍 DEBUG - Información del bloque:');
                console.log('- Tipo de bloque (blockType):', blockType);
                console.dir(blockType);
                console.log('- Datos del bloque (blockData):');
                console.dir(blockData);
                
                if (!blockType || blockType.trim() === '') {
                    throw new Error('Block type is empty or undefined');
                }
                
                if (!blockData || typeof blockData !== 'object') {
                    throw new Error('Block data is empty or not an object');
                }
                
                const completeBlock = {
                    type: blockType,
                    data: blockData
                };
                
                console.log('📦 Estructura completa del bloque:');
                console.dir(completeBlock);
                
                const jsonData = JSON.stringify(completeBlock, null, 2);
                
                console.log('📝 JSON generado para copiar:');
                console.log(jsonData);
                
                console.log('🔧 Verificando soporte de clipboard:');
                console.log('- navigator.clipboard existe:', !!navigator.clipboard);
                console.log('- navigator.clipboard.writeText existe:', !!(navigator.clipboard && navigator.clipboard.writeText));
                
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    console.log('✅ Usando API moderna de clipboard...');
                    await navigator.clipboard.writeText(jsonData);
                    console.log('🎉 Bloque copiado al portapapeles exitosamente');
                    $wire.dispatch('notify', {
                        type: 'success',
                        title: 'Bloque copiado',
                        body: 'El bloque se ha copiado al portapapeles correctamente'
                    });
                } else {
                    console.log('⚠️ API moderna no disponible, usando fallback...');
                    const textArea = document.createElement('textarea');
                    textArea.value = jsonData;
                    textArea.style.position = 'fixed';
                    textArea.style.left = '-9999px';
                    textArea.style.top = '-9999px';
                    document.body.appendChild(textArea);
                    textArea.focus();
                    textArea.select();
                    
                    console.log('📋 Intentando copiar con document.execCommand...');
                    const successful = document.execCommand('copy');
                    console.log('🔧 Resultado de execCommand:', successful);
                    
                    document.body.removeChild(textArea);
                    
                    if (successful) {
                        console.log('🎉 Bloque copiado usando fallback exitosamente');
                        $wire.dispatch('notify', {
                            type: 'success',
                            title: 'Bloque copiado',
                            body: 'El bloque se ha copiado al portapapeles correctamente'
                        });
                    } else {
                        throw new Error('execCommand failed - el navegador no permitió la copia');
                    }
                }
            } catch (error) {
                console.log('❌ ERROR AL COPIAR BLOQUE:');
                console.error('Error completo:', error);
                console.dir(error);
                console.log('Mensaje de error:', error.message);
                console.log('Stack trace:', error.stack);
                
                $wire.dispatch('notify', {
                    type: 'error',
                    title: 'Error al copiar',
                    body: 'No se pudo copiar el bloque. Error: ' + error.message
                });
            }
            
            console.log('=== FIN COPIA DE BLOQUE ===');
        }
    }">
    <x-filament::icon-button
        color="gray"
        icon="heroicon-o-clipboard-document"
        size="xs"
        tooltip="Copiar bloque"
        x-on:click.stop="copyBlock()"
    />
</li>
