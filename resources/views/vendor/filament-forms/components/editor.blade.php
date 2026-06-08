@php
    use Filament\Forms\Components\Actions\Action;

    $containers = $getChildComponentContainers();
    $blockPickerBlocks = $getBlockPickerBlocks();
    $blockPickerColumns = $getBlockPickerColumns();
    $blockPickerWidth = $getBlockPickerWidth();
    $hasBlockPreviews = $hasBlockPreviews();
    $hasInteractiveBlockPreviews = $hasInteractiveBlockPreviews();

    $addAction = $getAction($getAddActionName());
    $addBetweenAction = $getAction($getAddBetweenActionName());
    $cloneAction = $getAction($getCloneActionName());
    $collapseAllAction = $getAction($getCollapseAllActionName());
    $editAction = $getAction($getEditActionName());
    $expandAllAction = $getAction($getExpandAllActionName());
    $deleteAction = $getAction($getDeleteActionName());
    $moveDownAction = $getAction($getMoveDownActionName());
    $moveUpAction = $getAction($getMoveUpActionName());
    $reorderAction = $getAction($getReorderActionName());
    $extraItemActions = $getExtraItemActions();

    $isAddable = $isAddable();
    $isCloneable = $isCloneable();
    $isCollapsible = $isCollapsible();
    $isDeletable = $isDeletable();
    $isReorderableWithButtons = $isReorderableWithButtons();
    $isReorderableWithDragAndDrop = $isReorderableWithDragAndDrop();

    $collapseAllActionIsVisible = $isCollapsible && $collapseAllAction->isVisible();
    $expandAllActionIsVisible = $isCollapsible && $expandAllAction->isVisible();

    $statePath = $getStatePath();
    $key = $getKey();

    $cssUrl = Vite::asset('resources/css/app.css');

    $reorderItems = [];
    foreach ($containers as $uuid => $item) {
        $label = $item->getParentComponent()->getLabel($item->getRawState(), $uuid)
            ?? __('Bloque') . ' ' . (count($reorderItems) + 1);

        $reorderItems[] = [
            'id' => $uuid,
            'label' => $label,
        ];
    }
    $reorderItemsJson = json_encode($reorderItems);
    $reorderItemsJson = str_replace('"', '&quot;', $reorderItemsJson);
    $reorderItemsJson = str_replace("'", "\\'", $reorderItemsJson);
    $reorderItemsJson = str_replace('\\', '\\\\', $reorderItemsJson);
@endphp

@once
    <style>
        .fi-visual-editor .fi-fo-field-wrp-error-message,
        .fi-visual-editor .fi-fo-field-wrp-hint {
            max-width: 80rem;
            margin-inline: auto;
        }

        .fi-visual-editor__devices,
        .fi-visual-editor__device-actions,
        .fi-visual-editor__actions {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
        }

        .fi-visual-editor__device-actions {
            gap: 0.5rem;
        }

        .fi-visual-editor__canvas {
            display: grid;
            gap: 1rem;
            max-width: 100%;
            margin-inline: auto;
            transition: width 150ms ease;
        }

        .fi-visual-editor__items {
            display: grid;
            gap: 0;
            padding: 0;
            margin: 0;
            list-style: none;
            transition: opacity 150ms ease;
        }

        .fi-visual-editor__item {
            position: relative;
            border-radius: 0;
            overflow: hidden;
            transition: opacity 150ms ease;
        }

        .fi-visual-editor__items:hover .fi-visual-editor__item {
            opacity: 0.6;
        }

        .fi-visual-editor__items:hover .fi-visual-editor__item:hover {
            opacity: 1;
        }

        .fi-visual-editor__item.fi-collapsed {
            overflow: hidden;
        }

        .fi-visual-editor__item-header {
            position: absolute;
            inset: 0.5rem 0.5rem auto;
            z-index: 10;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            min-height: 3rem;
            padding: 0.75rem 1rem;
            border-radius: 0.75rem;
            color: var(--gray-100);
            background: rgb(9 9 11 / 0.88);
            box-shadow: 0 1px 2px rgb(0 0 0 / 0.15);
            cursor: pointer;
            opacity: 0;
            transition: opacity 150ms ease;
            user-select: none;
        }

        .fi-visual-editor__item:hover .fi-visual-editor__item-header,
        .fi-visual-editor__item.fi-collapsed .fi-visual-editor__item-header {
            opacity: 1;
        }

        .fi-visual-editor__item-header-list,
        .fi-visual-editor__item-actions {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0;
            margin: 0;
            list-style: none;
        }

        .fi-visual-editor__item-actions {
            margin-inline-start: auto;
        }

        .fi-visual-editor__collapse-action {
            position: relative;
            transition: transform 150ms ease;
        }

        .fi-visual-editor__item-title {
            min-width: 0;
            margin: 0;
            color: white;
            font-size: 0.875rem;
            font-weight: 600;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .fi-visual-editor__item-debug {
            margin-inline-start: 0.5rem;
            color: #00ff00;
            font-size: 0.6875rem;
            font-weight: 500;
        }

        .fi-visual-editor__item-content {
            position: relative;
            border-top: 1px solid var(--gray-100);
        }

        .dark .fi-visual-editor__item-content {
            border-top-color: rgb(255 255 255 / 0.1);
        }

        .fi-visual-editor__preview {
            position: relative;
        }

        .fi-visual-editor__preview-overlay {
            position: absolute;
            inset: 0;
            z-index: 1;
            cursor: pointer;
        }

        .fi-visual-editor__between-add {
            position: relative;
            z-index: 10;
            height: 0;
            margin-top: 0 !important;
        }

        .fi-visual-editor__between-add-inner {
            display: flex;
            justify-content: center;
            width: 100%;
            transition: opacity 75ms ease;
        }

        .fi-visual-editor__between-add-inner .fi-btn {
            background: #333;
            transform: translateY(-15px);
        }

        .fi-visual-editor__paste-skeleton {
            padding: 0.75rem;
        }

        .fi-visual-editor__paste-skeleton-card {
            border: 1px solid var(--gray-200);
            border-radius: 0.75rem;
            overflow: hidden;
            background: white;
        }

        .dark .fi-visual-editor__paste-skeleton-card {
            border-color: rgb(255 255 255 / 0.1);
            background: var(--gray-900);
        }

        .fi-visual-editor__paste-skeleton-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid var(--gray-200);
        }

        .dark .fi-visual-editor__paste-skeleton-header {
            border-bottom-color: rgb(255 255 255 / 0.1);
        }

        .fi-visual-editor__paste-skeleton-body {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            padding: 2rem 1rem;
        }

        .fi-visual-editor__paste-skeleton-dot {
            width: 0.5rem;
            height: 0.5rem;
            border-radius: 999px;
            background: var(--gray-300);
            animation: sk-pulse 1.2s ease-in-out infinite;
        }

        .dark .fi-visual-editor__paste-skeleton-dot {
            background: var(--gray-600);
        }

        .fi-visual-editor__paste-skeleton-dot:nth-child(2) {
            animation-delay: 0.2s;
        }

        .fi-visual-editor__paste-skeleton-dot:nth-child(3) {
            animation-delay: 0.4s;
        }

        @keyframes sk-pulse {
            0%, 80%, 100% { transform: scale(0.6); opacity: 0.3; }
            40% { transform: scale(1); opacity: 1; }
        }

        .fi-visual-editor__paste-skeleton-shine {
            width: 4rem;
            height: 0.75rem;
            border-radius: 999px;
            background: linear-gradient(90deg, var(--gray-200) 25%, var(--gray-100) 50%, var(--gray-200) 75%);
            background-size: 200% 100%;
            animation: sk-shine 1.5s ease-in-out infinite;
        }

        .dark .fi-visual-editor__paste-skeleton-shine {
            background: linear-gradient(90deg, var(--gray-700) 25%, var(--gray-600) 50%, var(--gray-700) 75%);
            background-size: 200% 100%;
        }

        @keyframes sk-shine {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        .fi-visual-editor__between-label {
            position: relative;
            border-top: 1px solid var(--gray-200);
        }

        .dark .fi-visual-editor__between-label {
            border-top-color: rgb(255 255 255 / 0.1);
        }

        .fi-visual-editor__between-label span {
            position: absolute;
            top: -0.75rem;
            left: 0.75rem;
            padding-inline: 0.25rem;
            font-size: 0.875rem;
            font-weight: 500;
            background: var(--gray-50);
        }

        .dark .fi-visual-editor__between-label span {
            background: var(--gray-950);
        }

        .fi-visual-editor__empty {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 8rem;
            color: var(--gray-500);
        }

        .dark .fi-visual-editor__empty {
            color: var(--gray-400);
        }

        .fi-visual-editor__loading {
            display: block;
            height: 250px;
            background: var(--gray-100);
            animation: fi-visual-editor-pulse 2s infinite;
        }

        .dark .fi-visual-editor__loading {
            background: var(--gray-800);
        }

        .fi-visual-editor__hidden-block {
            position: absolute;
            inset: 0;
            z-index: 2;
            display: grid;
            place-items: center;
            background: rgb(0 0 0 / 0.5);
            color: white;
            font-weight: 600;
            backdrop-filter: blur(1px);
        }

        .fi-visual-editor__reorder-modal {
            position: fixed;
            inset: 0;
            width: min(100%, 28rem);
            max-height: 90vh;
            margin: auto;
            padding: 0;
            border: 0;
            border-radius: 0.75rem;
            background: var(--gray-50);
            color: var(--gray-950);
            box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.2), 0 8px 10px -6px rgb(0 0 0 / 0.2);
        }

        .fi-visual-editor__reorder-modal::backdrop {
            background: rgb(3 7 18 / 0.5);
        }

        .dark .fi-visual-editor__reorder-modal {
            background: var(--gray-900);
            color: white;
        }

        .fi-visual-editor__reorder-header,
        .fi-visual-editor__reorder-footer {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            padding: 1rem;
            border-bottom: 1px solid var(--gray-200);
        }

        .fi-visual-editor__reorder-footer {
            justify-content: flex-end;
            border-top: 1px solid var(--gray-200);
            border-bottom: 0;
        }

        .dark .fi-visual-editor__reorder-header,
        .dark .fi-visual-editor__reorder-footer {
            border-color: rgb(255 255 255 / 0.1);
        }

        .fi-visual-editor__reorder-title {
            margin: 0;
            font-size: 1.125rem;
            font-weight: 600;
        }

        .fi-visual-editor__reorder-description {
            margin: 0.25rem 0 0;
            color: var(--gray-500);
            font-size: 0.875rem;
        }

        .dark .fi-visual-editor__reorder-description {
            color: var(--gray-400);
        }

        .fi-visual-editor__reorder-body {
            max-height: 24rem;
            padding: 1rem;
            overflow-y: auto;
        }

        .fi-visual-editor__reorder-list {
            display: grid;
            gap: 0.5rem;
            padding: 0;
            margin: 0;
            list-style: none;
        }

        .fi-visual-editor__reorder-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem 0.75rem;
            border: 1px solid var(--gray-200);
            border-radius: 0.5rem;
            background: var(--gray-100);
            cursor: grab;
            user-select: none;
            transition: transform 150ms ease, opacity 150ms ease, border-color 150ms ease;
        }

        .dark .fi-visual-editor__reorder-item {
            border-color: rgb(255 255 255 / 0.1);
            background: var(--gray-800);
        }

        .fi-visual-editor__reorder-handle {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 2rem;
            height: 2rem;
            color: var(--gray-400);
        }

        .fi-visual-editor__reorder-label {
            flex: 1;
            min-width: 0;
            color: var(--gray-700);
            font-size: 0.875rem;
            font-weight: 500;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .dark .fi-visual-editor__reorder-label {
            color: var(--gray-200);
        }

        .fi-visual-editor__icon {
            width: 1.25rem;
            height: 1.25rem;
        }

        .fi-visual-editor__close-button {
            color: var(--gray-400);
            background: transparent;
            border: 0;
            cursor: pointer;
        }

        .fi-visual-editor__close-button:hover {
            color: var(--gray-500);
        }

        .dark .fi-visual-editor__close-button:hover {
            color: var(--gray-300);
        }

        @keyframes fi-visual-editor-pulse {
            0%, 100% { opacity: 0.5; }
            50% { opacity: 1; }
        }
    </style>
@endonce

<script>
if (!window.__reorderItems) window.__reorderItems = {};
window.__reorderItems['{{ $statePath }}'] = @js($reorderItems);

window.__blockPreviewCSS = {
    app: "{{ $cssUrl }}",
    liteYoutube: "https://cdn.jsdelivr.net/npm/@lite-youtube/lite-youtube-embed@0.3.0/lite-yt-embed.css",
};

window.__blockPreviewResetStyles = `
    body, #main { background: #fff; }
    swiper-container, swiper-slide, lite-youtube { display: block; }
    swiper-container { display: flex !important; gap: 12px; overflow-x: auto; }
    swiper-slide { flex-shrink: 0; width: 85%; min-width: 260px; }
    @media (min-width: 768px) { swiper-slide { width: 45%; } }
    @media (min-width: 1024px) { swiper-slide { width: 33%; } }
    @media (min-width: 1280px) { swiper-slide { width: 28%; } }

    #preview-scroll, #preview-scroll *, #main, #main * { pointer-events: none !important; user-select: none !important; }
    a, button, [onclick], [role="button"] { pointer-events: none !important; }

    #preview-scroll {
        height: 100%;
    }
`;
</script>
<script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/ScrollTrigger.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/ScrollToPlugin.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/TextPlugin.min.js"></script>
<script>gsap.registerPlugin(ScrollTrigger, ScrollToPlugin);</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    function initPreviewManager() {
        if (typeof Livewire === 'undefined') {
            setTimeout(initPreviewManager, 100);
            return;
        }
        if (window.blockPreviewManager) return;

        window.__ScriptBridge = {
            executeInShadowContext(shadowRoot, code) {
                const overrides = {
                    querySelector: (sel) => shadowRoot.querySelector(sel) || originals.querySelector(sel),
                    querySelectorAll: (sel) => {
                        const shadow = [...shadowRoot.querySelectorAll(sel)];
                        const doc = [...originals.querySelectorAll(sel)];
                        return [...shadow, ...doc.filter(el => !shadow.includes(el))];
                    },
                    getElementById: (id) => shadowRoot.getElementById(id) || originals.getElementById(id),
                };
                const originals = {};
                for (const [key, fn] of Object.entries(overrides)) {
                    originals[key] = document[key].bind(document);
                    document[key] = fn;
                }
                let origScrollTriggerCreate = null;
                if (window.ScrollTrigger) {
                    origScrollTriggerCreate = ScrollTrigger.create.bind(ScrollTrigger);
                    const scroller = shadowRoot.querySelector('#preview-scroll');
                    ScrollTrigger.create = function(vars) {
                        if (scroller && !vars.scroller) {
                            vars.scroller = scroller;
                        }
                        return origScrollTriggerCreate(vars);
                    };
                }
                try {
                    (new Function(code))();
                } catch (e) {
                    console.warn('[PreviewBridge] Script:', e.message);
                } finally {
                    for (const [key, fn] of Object.entries(originals)) {
                        document[key] = fn;
                    }
                    if (origScrollTriggerCreate) {
                        ScrollTrigger.create = origScrollTriggerCreate;
                    }
                }
            },
            processBlockHTML(html) {
                const scripts = [];
                const clean = html.replace(
                    /<script(?:\s[^>]*)?>([\s\S]*?)<\/script>/gi,
                    (match, code) => {
                        if (code.trim()) scripts.push(code.trim());
                        return '';
                    }
                );
                return { cleanHtml: clean, scripts };
            },
        };

        window.blockPreviewManager = {
            instances: new Map(),
            createInstance(hostId, contentId, alpineComponent) {
                const old = window.blockPreviewManager.instances.get(hostId);
                if (old) old.cleanup();
                const instance = {
                    hostId, contentId, alpineComponent,
                    host: null, shadowRoot: null, contentElement: null,
                    lastContentHash: '', observer: null,
                    init() {
                        this.host = document.getElementById(this.hostId);
                        this.contentElement = document.getElementById(this.contentId);
                        if (!this.host || !this.contentElement) return false;
                        window.blockPreviewManager.instances.set(this.hostId, this);
                        if (this.host.shadowRoot) {
                            this.shadowRoot = this.host.shadowRoot;
                        } else {
                            const css = window.__blockPreviewCSS;
                            this.shadowRoot = this.host.attachShadow({mode: 'open'});
                            this.shadowRoot.innerHTML = `
                                <link rel="stylesheet" href="${css.app}">
                                <link rel="stylesheet" href="${css.liteYoutube}">
                                <style>
                                    ${window.__blockPreviewResetStyles}
                                </style>
                                <div id="preview-scroll">
                                    <div id="main"></div>
                                </div>
                            `;
                        }
                        this.alpineComponent.loading = false;
                        if (!this.updateContent(true)) {
                            this.alpineComponent.loading = true;
                            setTimeout(() => this.init(), 100);
                            return;
                        }
                        this.setupObserver();
                        return true;
                    },
                    rebuild() {
                        if (!this.host || this.shadowRoot) return;
                        if (this.observer) { this.observer.disconnect(); this.observer = null; }
                        if (this.host.shadowRoot) {
                            this.shadowRoot = this.host.shadowRoot;
                            this.updateContent(true);
                            this.setupObserver();
                            return;
                        }
                        const css = window.__blockPreviewCSS;
                        this.shadowRoot = this.host.attachShadow({mode: 'open'});
                        this.shadowRoot.innerHTML = `
                            <link rel="stylesheet" href="${css.app}">
                            <link rel="stylesheet" href="${css.liteYoutube}">
                            <style>
                                ${window.__blockPreviewResetStyles}
                            </style>
                            <div id="preview-scroll">
                                <div id="main"></div>
                            </div>
                        `;
                        this.alpineComponent.loading = false;
                        this.updateContent(true);
                        this.setupObserver();
                    },
                    getContentHash(content) {
                        let hash = 0;
                        for (let i = 0; i < content.length; i++) {
                            hash = ((hash << 5) - hash) + content.charCodeAt(i);
                            hash = hash & hash;
                        }
                        return hash.toString();
                    },
                    updateContent(force = false) {
                        try {
                            if (!this.shadowRoot) return false;
                            this.contentElement = document.getElementById(this.contentId);
                            if (!this.contentElement) return false;
                            const MAIN = this.shadowRoot.querySelector('#main');
                            if (!MAIN) return false;
                            const content = this.contentElement.innerHTML;
                            if (!content?.trim()) return false;
                            const hash = this.getContentHash(content);
                            if (!force && this.lastContentHash === hash) return true;
                            const { cleanHtml, scripts } = window.__ScriptBridge.processBlockHTML(content);
                            MAIN.innerHTML = cleanHtml;
                            scripts.forEach(code => {
                                window.__ScriptBridge.executeInShadowContext(this.shadowRoot, code);
                            });
                            if (window.Alpine && cleanHtml.includes('x-data')) {
                                var handler = function(e) {
                                    if (e.error && e.error.el && MAIN.contains(e.error.el)) {
                                        e.preventDefault();
                                    }
                                };
                                window.addEventListener('error', handler);
                                try {
                                    Alpine.initTree(MAIN);
                                } catch (e) {
                                    console.warn('[PreviewBridge] Alpine:', e.message);
                                }
                                setTimeout(function() {
                                    window.removeEventListener('error', handler);
                                }, 200);
                            }
                            if (window.ScrollTrigger) {
                                ScrollTrigger.refresh(true);
                            }
                            window.dispatchEvent(new CustomEvent('preview-content-updated', {
                                detail: { shadowRoot: this.shadowRoot, mainElement: MAIN }
                            }));
                            this.lastContentHash = hash;
                            return true;
                        } catch (e) {
                            console.warn('[PreviewBridge] updateContent:', e.message);
                            return false;
                        }
                    },
                    setupObserver() {
                        if (this.observer) return;
                        let debounceTimer;
                        this.observer = new MutationObserver(() => {
                            clearTimeout(debounceTimer);
                            debounceTimer = setTimeout(() => {
                                if (this.shadowRoot) {
                                    this.updateContent();
                                }
                            }, 150);
                        });
                        if (this.contentElement) {
                            this.observer.observe(this.contentElement, {
                                childList: true,
                                subtree: true,
                                characterData: true
                            });
                        }
                    },
                    checkAndUpdate() {
                        this.host = document.getElementById(this.hostId);
                        this.contentElement = document.getElementById(this.contentId);
                        if (!this.host || !this.contentElement) return;
                        if (!this.host.shadowRoot) {
                            this.shadowRoot = null;
                            this.observer?.disconnect();
                            this.observer = null;
                            this.rebuild();
                            return;
                        }
                        this.shadowRoot = this.host.shadowRoot;
                        this.updateContent();
                    },
                    forceReload() {
                        this.lastContentHash = '';
                        this.updateContent(true);
                    },
                    cleanup() {
                        if (this.observer) this.observer.disconnect();
                        window.blockPreviewManager.instances.delete(this.hostId);
                    }
                };
                return instance;
            },
            reloadAll() {
                this.instances.forEach(i => i.updateContent(true));
            }
        };

        Livewire.hook('commit', ({ component, commit, respond }) => {
            setTimeout(() => {
                window.blockPreviewManager.instances.forEach(i => i.checkAndUpdate());
            }, 300);
        });
    }
    initPreviewManager();
});
</script>

<x-dynamic-component class="fi-visual-editor" :component="$getFieldWrapperView()" :field="$field" x-data="{
    currentDevice: 'desktop',
    cntWidth: window.innerWidth > 1280 ? '1280' : '320px',
    reorderInProgress: false,
    getDeviceWidth: (device = 'desktop') => {
        let newWidth;
        switch (device) {
            case 'mobile':
                newWidth = '320px';
                break;
            case 'tablet':
                newWidth = '768px';
                break;
            case 'desktop':
                newWidth = '1280px';
                break;
            default:
                newWidth = '1280px';
        }
        return newWidth;
    },
}"
    x-init="document.addEventListener('sortable:start', () => {
        reorderInProgress = true;
    });

    Livewire.hook('commit', ({ component, commit, respond }) => {
        if (reorderInProgress) {
            setTimeout(() => {
                window.dispatchEvent(new CustomEvent('reload-all-previews', {
                    detail: { statePath: '{{ $statePath }}' }
                }));
                reorderInProgress = false;
            }, 800);
        }
    });

    Alpine.store('builderReorder_{{ $statePath }}', {
        items: (window.__reorderItems || {})['{{ $statePath }}'] || [],
        set(items) { this.items = items || []; },
    });
">
    @include('filament-forms::components.editor.device-selector')

    @persist('editor')
    {{-- Hidden input for paste functionality --}}
    <input type="hidden" id="blocks_pastable_{{ $statePath }}" wire:model="blocks_pastable" />

    <div class="fi-visual-editor__canvas" x-bind:style="{ 'width': cntWidth }"
        {{ $attributes->merge($getExtraAttributes(), escape: false)->class(['fi-fo-builder grid gap-y-4']) }}
        @include('filament-forms::components.editor.paste-handler-alpine', ['statePath' => $statePath])>

        @if (count($containers))
            <ul x-sortable data-sortable-animation-duration="{{ $getReorderAnimationDuration() }}"
                x-on:end.stop="$wire.mountAction('reorder', { items: $event.target.sortable.toArray() }, { schemaComponent: '{{ $key }}' })"
                class="fi-visual-editor__items">
                @php
                    $hasBlockLabels = $hasBlockLabels();
                    $hasBlockIcons = $hasBlockIcons();
                    $hasBlockNumbers = $hasBlockNumbers();
                @endphp

                @foreach ($containers as $uuid => $item)
                    @php
                        $visibleExtraItemActions = array_filter(
                            $extraItemActions,
                            fn(Action $action): bool => $action(['item' => $uuid])->isVisible(),
                        );
                        $cloneAction = $cloneAction(['item' => $uuid]);
                        $cloneActionIsVisible = $isCloneable && $cloneAction->isVisible();
                        $deleteAction = $deleteAction(['item' => $uuid]);
                        $deleteActionIsVisible = $isDeletable && $deleteAction->isVisible();
                        $editAction = $editAction(['item' => $uuid]);
                        $editActionIsVisible = $hasBlockPreviews && $editAction->isVisible();
                        $moveDownAction = $moveDownAction(['item' => $uuid])->disabled($loop->last);
                        $moveDownActionIsVisible = $isReorderableWithButtons && $moveDownAction->isVisible();
                        $moveUpAction = $moveUpAction(['item' => $uuid])->disabled($loop->first);
                        $moveUpActionIsVisible = $isReorderableWithButtons && $moveUpAction->isVisible();
                        $reorderActionIsVisible = $isReorderableWithDragAndDrop && $reorderAction->isVisible();
                    @endphp

                    <li wire:key="{{ $this->getId() }}.{{ $item->getStatePath() }}.{{ $field::class }}.item"
                        x-data="{ isCollapsed: @js($isCollapsed($item)) }"
                        x-on:builder-expand.window="$event.detail === '{{ $statePath }}' && (isCollapsed = false)"
                        x-on:builder-collapse.window="$event.detail === '{{ $statePath }}' && (isCollapsed = true)"
                        x-on:expand="isCollapsed = false" x-sortable-item="{{ $uuid }}" class="fi-visual-editor__item"
                        x-bind:class="{ 'fi-collapsed': isCollapsed }">

                        @include('filament-forms::components.editor.block-header', [
                            'item' => $item,
                            'uuid' => $uuid,
                            'statePath' => $statePath,
                            'loop' => $loop,
                            'isCollapsible' => $isCollapsible,
                            'hasBlockIcons' => $hasBlockIcons,
                            'hasBlockLabels' => $hasBlockLabels,
                            'hasBlockNumbers' => $hasBlockNumbers,
                            'isBlockLabelTruncated' => $isBlockLabelTruncated(),
                            'reorderAction' => $reorderAction,
                            'reorderActionIsVisible' => $reorderActionIsVisible,
                            'moveUpAction' => $moveUpAction,
                            'moveUpActionIsVisible' => $moveUpActionIsVisible,
                            'moveDownAction' => $moveDownAction,
                            'moveDownActionIsVisible' => $moveDownActionIsVisible,
                            'editAction' => $editAction,
                            'editActionIsVisible' => $editActionIsVisible,
                            'cloneAction' => $cloneAction,
                            'cloneActionIsVisible' => $cloneActionIsVisible,
                            'deleteAction' => $deleteAction,
                            'deleteActionIsVisible' => $deleteActionIsVisible,
                            'visibleExtraItemActions' => $visibleExtraItemActions,
                            'getAction' => $getAction,
                        ])

                        @include('filament-forms::components.editor.block-preview', [
                            'item' => $item,
                            'loop' => $loop,
                            'hasBlockPreviews' => $hasBlockPreviews,
                            'hasInteractiveBlockPreviews' => $hasInteractiveBlockPreviews,
                            'editActionIsVisible' => $editActionIsVisible,
                            'statePath' => $statePath,
                            'key' => $key,
                            'uuid' => $uuid,
                        ])
                    </li>

                    @if (!$loop->last)
                        @if ($isAddable && $addBetweenAction(['afterItem' => $uuid])->isVisible())
                            <li class="fi-visual-editor__between-add">
                                <div
                                    class="fi-visual-editor__between-add-inner">
                                    <div class="bg-white rounded-lg fi-fo-builder-block-picker-ctn dark:bg-gray-900">
                                        <x-filament-forms::builder.block-picker :action="$addBetweenAction" :after-item="$uuid"
                                            :columns="$blockPickerColumns" :blocks="$blockPickerBlocks" :key="$key" :state-path="$statePath" :width="$blockPickerWidth">
                                            <x-slot name="trigger">
                                                {{ $addBetweenAction(['afterItem' => $uuid]) }}
                                            </x-slot>
                                        </x-filament-forms::builder.block-picker>
                                    </div>
                                </div>
                            </li>
                        @elseif (filled($labelBetweenItems = $getLabelBetweenItems()))
                            <li class="fi-visual-editor__between-label">
                                <span>
                                    {{ $labelBetweenItems }}
                                </span>
                            </li>
                        @endif
                    @endif
                @endforeach
            </ul>

            <div x-cloak x-show="pasting" class="fi-visual-editor__paste-skeleton">
                <div class="fi-visual-editor__paste-skeleton-card">
                    <div class="fi-visual-editor__paste-skeleton-header">
                        <div class="fi-visual-editor__paste-skeleton-shine" style="width: 6rem;"></div>
                        <div class="fi-visual-editor__paste-skeleton-shine" style="width: 3rem;"></div>
                    </div>
                    <div class="fi-visual-editor__paste-skeleton-body">
                        <div class="fi-visual-editor__paste-skeleton-dot"></div>
                        <div class="fi-visual-editor__paste-skeleton-dot"></div>
                        <div class="fi-visual-editor__paste-skeleton-dot"></div>
                    </div>
                </div>
            </div>
        @else
            <div class="fi-visual-editor__empty">
                {{ __('Contruya un sitio increible.') }}
            </div>
        @endif
        <div class="fi-visual-editor__actions">
            @if ($isAddable && $addAction->isVisible())
                <x-filament-forms::builder.block-picker :action="$addAction" :blocks="$blockPickerBlocks" :columns="['default' => 2, 'sm' => 1]"
                    :key="$key" :state-path="$statePath" :width="$blockPickerWidth">
                    <x-slot name="trigger">
                        {{ $addAction }}
                    </x-slot>
                </x-filament-forms::builder.block-picker>
            @endif

            @include('filament-forms::components.editor.reorder-modal', [
                'statePath' => $statePath,
                'key' => $key,
                'reorderItems' => $reorderItems,
            ])

            <div class="fi-visual-editor__actions">
                @include('filament-forms::components.editor.paste-button', ['statePath' => $statePath])
            </div>
        </div>
    </div>
    @endpersist('editor')
</x-dynamic-component>
