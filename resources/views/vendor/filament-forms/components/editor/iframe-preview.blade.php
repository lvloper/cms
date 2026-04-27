{{-- IFrame Preview Component --}}
@props(['item', 'loop', 'uuid'])

@php
    $blockUuid = $uuid ?? uniqid('block-');
    $iframeId = 'iframe-' . $blockUuid;
    $contentId = 'content-' . $blockUuid;
@endphp

<div 
    class="block isolate relative" 
    data-block-uuid="{{ $blockUuid }}"
    x-data="{
        loading: true,
        iframeId: '{{ $iframeId }}',
        contentId: '{{ $contentId }}',
        instance: null,
        
        init() {
            // Crear instancia del manager
            this.instance = window.blockPreviewManager.createInstance(this.iframeId, this.contentId, this);
            this.instance.init();
        },
        
        matchHeight() {
            this.instance?.matchHeight();
        },
        
        forceReload() {
            this.instance?.forceReload();
        }
    }"
>
    <div class="block bg-gray-100 pulse-opacity dark:bg-gray-800" 
        x-show="loading"
        style="height: 250px">
    </div>
    
    <iframe
        id="{{ $iframeId }}"
        x-cloak 
        x-show="!loading" 
        src="{{ route('preview.blocks') }}"
        loading="lazy" 
        frameborder="0" 
        style="width: 100%; min-height: 250px; height: 250px; overflow: hidden;">
    </iframe>

    <div class="hidden" id="{{ $contentId }}">
        @include('filament-forms::components.editor.block-render-content', ['item' => $item])
    </div>
</div>

@once
<script>
// Sistema global de gestión de previews
document.addEventListener('DOMContentLoaded', function() {
    // Esperar a que Livewire esté disponible
    function initPreviewManager() {
        if (typeof Livewire === 'undefined') {
            console.log('⏳ Waiting for Livewire...');
            setTimeout(initPreviewManager, 100);
            return;
        }
        
        if (window.blockPreviewManager) {
            console.log('ℹ️ Preview manager already initialized');
            return;
        }
        
        console.log('✅ Initializing preview manager');
        
        window.blockPreviewManager = {
            instances: new Map(),
            
            createInstance(iframeId, contentId, alpineComponent) {
                const instance = {
                    iframeId,
                    contentId,
                    alpineComponent,
                    iframe: null,
                    contentElement: null,
                    lastContentHash: '',
                    observer: null,
                    
                    init() {
                        this.iframe = document.getElementById(this.iframeId);
                        this.contentElement = document.getElementById(this.contentId);
                        
                        if (!this.iframe || !this.contentElement) {
                            console.error('❌ Elements not found:', this.iframeId, this.contentId);
                            return;
                        }
                        
                        console.log('✅ Initializing preview:', this.iframeId);
                        
                        // Registrar en el manager
                        window.blockPreviewManager.instances.set(this.iframeId, this);
                        
                        // Verificar si el iframe ya está cargado
                        const isIframeLoaded = this.iframe.contentDocument && 
                                             this.iframe.contentDocument.readyState === 'complete';
                        
                        if (isIframeLoaded) {
                            console.log('✅ Iframe already loaded:', this.iframeId);
                            this.alpineComponent.loading = false;
                            this.waitForIframeReady();
                        } else {
                            // Setup iframe onload para cuando cargue
                            this.iframe.onload = () => {
                                console.log('✅ Iframe loaded:', this.iframeId);
                                this.alpineComponent.loading = false;
                                this.waitForIframeReady();
                            };
                        }
                        
                        // NO inicializar el observer hasta después de la primera carga exitosa
                    },
                    
                    waitForIframeReady(retries = 0, maxRetries = 20) {
                        const checkAndUpdate = () => {
                            if (!this.iframe?.contentDocument) {
                                console.warn('⚠️ Iframe document not ready, retrying...', retries);
                                if (retries < maxRetries) {
                                    setTimeout(() => this.waitForIframeReady(retries + 1, maxRetries), 200);
                                }
                                return;
                            }
                            
                            const mainElement = this.iframe.contentDocument.querySelector('#main');
                            if (!mainElement) {
                                console.warn('⚠️ #main not found in iframe, retrying...', retries);
                                if (retries < maxRetries) {
                                    setTimeout(() => this.waitForIframeReady(retries + 1, maxRetries), 200);
                                } else {
                                    console.error('❌ #main never found in iframe after', maxRetries, 'retries');
                                }
                                return;
                            }
                            
                            console.log('✅ Iframe ready, updating content');
                            const success = this.updateContent(true);
                            
                            // Solo inicializar el observer después de la primera carga exitosa
                            if (success && !this.observer) {
                                console.log('👀 Starting observer:', this.contentId);
                                this.setupObserver();
                            }
                        };
                        
                        // Ejecutar después de un delay mayor para dar tiempo al iframe
                        setTimeout(checkAndUpdate, 200);
                    },
                    
                    setupObserver() {
                        if (this.observer) {
                            return; // Ya existe
                        }
                        
                        // Debounce para evitar múltiples actualizaciones rápidas
                        let debounceTimer;
                        this.observer = new MutationObserver(() => {
                            clearTimeout(debounceTimer);
                            debounceTimer = setTimeout(() => {
                                console.log('🔍 Content mutation detected:', this.contentId);
                                // Solo actualizar si el iframe está realmente listo
                                if (this.iframe?.contentDocument?.querySelector('#main')) {
                                    this.updateContent();
                                }
                            }, 150); // Esperar 150ms de inactividad
                        });
                        
                        if (this.contentElement) {
                            this.observer.observe(this.contentElement, {
                                childList: true,
                                subtree: true,
                                characterData: true
                            });
                        }
                    },
                    
                    getContentHash(content) {
                        let hash = 0;
                        for (let i = 0; i < content.length; i++) {
                            const char = content.charCodeAt(i);
                            hash = ((hash << 5) - hash) + char;
                            hash = hash & hash;
                        }
                        return hash.toString();
                    },
                    
                    updateContent(force = false) {
                        try {
                            if (!this.iframe?.contentDocument?.body) {
                                console.warn('⚠️ Iframe not ready:', this.iframeId);
                                return false;
                            }
                            
                            // Re-buscar elementos por si fueron re-renderizados
                            this.contentElement = document.getElementById(this.contentId);
                            
                            if (!this.contentElement) {
                                console.warn('⚠️ Content element not found:', this.contentId);
                                return false;
                            }
                            
                            const MAIN = this.iframe.contentDocument.querySelector('#main');
                            if (!MAIN) {
                                console.warn('⚠️ #main element not found in iframe');
                                return false;
                            }
                            
                            const content = this.contentElement.innerHTML;
                            if (!content || !content.trim()) {
                                console.warn('⚠️ No content to inject:', this.contentId);
                                return false;
                            }
                            
                            // Verificar si el contenido cambió
                            const contentHash = this.getContentHash(content);
                            if (!force && this.lastContentHash === contentHash) {
                                console.log('ℹ️ Content unchanged:', this.contentId);
                                return true;
                            }
                            
                            // Inyectar contenido
                            MAIN.innerHTML = content;
                            this.lastContentHash = contentHash;
                            console.log('✅ Content injected:', this.contentId, '(length:', content.length, ')');
                            
                            // Reiniciar Alpine.js en el iframe
                            if (this.iframe.contentWindow?.Alpine) {
                                try {
                                    this.iframe.contentWindow.Alpine.initTree(MAIN);
                                } catch (e) {
                                    console.warn('Alpine init warning:', e);
                                }
                            }
                            
                            // Inicializar Swiper si existe
                            try {
                                if (typeof this.iframe.contentWindow.initSwiperElements === 'function') {
                                    this.iframe.contentWindow.initSwiperElements();
                                } else if (this.iframe.contentWindow.Swiper) {
                                    this.iframe.contentWindow.dispatchEvent(new Event('swiper:refresh'));
                                }
                            } catch (e) {
                                console.warn('Swiper init warning:', e);
                            }
                            
                            // Escuchar eventos de masonry si existe
                            this.setupMasonryListeners();
                            
                            // Usar reintentos para dar tiempo a imágenes/masonry/etc
                            this.matchHeightWithRetries();
                            
                            return true;
                            
                        } catch (e) {
                            console.error('❌ Error updating iframe:', this.iframeId, e);
                            return false;
                        }
                    },
                    
                    checkAndUpdate() {
                        // Re-buscar elementos por ID
                        this.iframe = document.getElementById(this.iframeId);
                        this.contentElement = document.getElementById(this.contentId);
                        
                        // Solo actualizar si tenemos todos los elementos necesarios
                        if (!this.iframe || !this.contentElement) {
                            console.warn('⚠️ Elements missing during checkAndUpdate:', this.iframeId);
                            return;
                        }
                        
                        // Verificar que el iframe esté realmente listo
                        if (!this.iframe.contentDocument?.querySelector('#main')) {
                            console.warn('⚠️ Iframe not ready during checkAndUpdate:', this.iframeId);
                            return;
                        }
                        
                        console.log('🔍 Checking for updates:', this.contentId);
                        this.updateContent();
                    },
                    
                    setupMasonryListeners() {
                        try {
                            if (!this.iframe?.contentWindow) return;
                            
                            const iframeWindow = this.iframe.contentWindow;
                            
                            // Listener para imagesLoaded
                            if (typeof iframeWindow.imagesLoaded === 'function') {
                                const masonryGrid = iframeWindow.document.querySelector('#masonry-grid');
                                if (masonryGrid) {
                                    console.log('🖼️ Waiting for images in masonry...');
                                    iframeWindow.imagesLoaded(masonryGrid, () => {
                                        console.log('✅ Images loaded, adjusting height');
                                        this.matchHeight();
                                    });
                                }
                            }
                            
                            // Listener para Masonry layoutComplete
                            if (typeof iframeWindow.Masonry === 'function') {
                                setTimeout(() => {
                                    const masonryGrid = iframeWindow.document.querySelector('#masonry-grid');
                                    if (masonryGrid && masonryGrid._masonry) {
                                        console.log('🧱 Setting up masonry listener');
                                        masonryGrid._masonry.on('layoutComplete', () => {
                                            console.log('✅ Masonry layout complete');
                                            this.matchHeight();
                                        });
                                    }
                                }, 500);
                            }
                        } catch (e) {
                            console.warn('Masonry listener setup failed:', e);
                        }
                    },
                    
                    matchHeight() {
                        try {
                            if (!this.iframe || !this.iframe.contentDocument) return;
                            
                            const iframeDoc = this.iframe.contentDocument;
                            const el = iframeDoc.querySelector('#main .block-preview');
                            
                            if (el) {
                                const mb = parseInt(window.getComputedStyle(el).getPropertyValue('margin-bottom')) || 0;
                                const calculatedHeight = el.offsetHeight + mb;
                                // Aplicar altura mínima de 250px
                                const h = Math.max(calculatedHeight, 250);
                                this.iframe.style.height = h + 'px';
                                console.log('📏 Height matched:', h + 'px', '(calculated:', calculatedHeight + 'px)', 'for', this.iframeId);
                            }
                        } catch (e) {
                            console.error('Error matching height:', e);
                        }
                    },
                    
                    matchHeightWithRetries(attempt = 0, maxAttempts = 5) {
                        // Reintentar con delays progresivos para dar tiempo a masonry/imágenes
                        const delays = [100, 300, 500, 1000, 2000];
                        
                        this.matchHeight();
                        
                        if (attempt < maxAttempts) {
                            setTimeout(() => {
                                this.matchHeightWithRetries(attempt + 1, maxAttempts);
                            }, delays[attempt]);
                        }
                    },
                    
                    forceReload() {
                        console.log('🔄 Force reload:', this.iframeId);
                        this.lastContentHash = '';
                        this.updateContent(true);
                    },
                    
                    cleanup() {
                        console.log('🧹 Cleaning up:', this.iframeId);
                        if (this.observer) {
                            this.observer.disconnect();
                        }
                        window.blockPreviewManager.instances.delete(this.iframeId);
                    }
                };
                
                return instance;
            },
            
            reloadAll() {
                console.log('🔄 Reloading all previews (' + this.instances.size + ')');
                this.instances.forEach((instance) => {
                    instance.updateContent(true);
                });
            }
        };
        
        // Detectar cuando Livewire termina de actualizar
        Livewire.hook('commit', ({ component, commit, respond }) => {
            setTimeout(() => {
                console.log('🔀 Livewire commit, checking all instances');
                window.blockPreviewManager.instances.forEach((instance) => {
                    instance.checkAndUpdate();
                });
            }, 300);
        });
    }
    
    // Iniciar cuando Livewire esté listo
    initPreviewManager();
});
</script>
@endonce
