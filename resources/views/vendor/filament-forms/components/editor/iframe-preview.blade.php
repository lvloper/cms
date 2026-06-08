{{-- Shadow DOM Preview Component --}}
@props(['item', 'loop', 'uuid'])

@php
    $blockUuid = $uuid ?? uniqid('block-');
    $hostId = 'shadow-' . $blockUuid;
    $contentId = 'content-' . $blockUuid;
@endphp

<div 
    style="display: block; isolation: isolate; position: relative;" 
    data-block-uuid="{{ $blockUuid }}"
    x-data="{
        loading: true,
        hostId: '{{ $hostId }}',
        contentId: '{{ $contentId }}',
        instance: null,
        
        init() {
            const ready = () => {
                if (!window.blockPreviewManager) return setTimeout(ready, 50);
                this.instance = window.blockPreviewManager.createInstance(this.hostId, this.contentId, this);
                if (!this.instance.init()) {
                    setTimeout(ready, 100);
                }
            };
            ready();
        },
        
        forceReload() {
            this.instance?.forceReload();
        }
    }"
>
    <div class="fi-visual-editor__loading" 
        x-show="loading"
        style="height: 250px">
    </div>
    
    <div
        id="{{ $hostId }}"
        x-cloak 
        x-show="!loading" 
        class="block-preview-shadow-host"
        style="width: 100%; display: block; background: #fff; min-height: 250px;">
    </div>

    <div hidden id="{{ $contentId }}">
        @include('filament-forms::components.editor.block-render-content', ['item' => $item])
    </div>
</div>
