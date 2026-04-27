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

    $reorderItems = [];
    foreach ($containers as $uuid => $item) {
        $label = $item->getParentComponent()->getLabel($item->getRawState(), $uuid)
            ?? __('Bloque') . ' ' . (count($reorderItems) + 1);

        $reorderItems[] = [
            'id' => $uuid,
            'label' => $label,
        ];
    }
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
            gap: 1rem;
            padding: 0;
            margin: 0;
            list-style: none;
        }

        .fi-visual-editor__item {
            position: relative;
            border-radius: 0.75rem;
            overflow: hidden;
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
            top: -1rem;
            z-index: 10;
            height: 0;
            margin-top: 0 !important;
        }

        .fi-visual-editor__between-add-inner {
            display: flex;
            justify-content: center;
            width: 100%;
            opacity: 0;
            transition: opacity 75ms ease;
        }

        .fi-visual-editor__between-add-inner:hover {
            opacity: 1;
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
    x-init="// Detectar cuando se inicia un reordenamiento
    document.addEventListener('sortable:start', () => {
        reorderInProgress = true;
        console.log('🎯 Sortable start detected');
    });

    // Detectar cuando termina el reordenamiento y Livewire actualiza
    Livewire.hook('commit', ({ component, commit, respond }) => {
        if (reorderInProgress) {
            console.log('🔀 Reorder commit detected');
            // Esperar a que Livewire y Alpine terminen de re-renderizar
            setTimeout(() => {
                console.log('� Dispatching reload-all-previews event');
                window.dispatchEvent(new CustomEvent('reload-all-previews', {
                    detail: { statePath: '{{ $statePath }}' }
                }));
                reorderInProgress = false;
            }, 800);
        }
    });

    Alpine.store('builderReorder_{{ $statePath }}', {
        items: @js($reorderItems),
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
                                            :columns="$blockPickerColumns" :blocks="$blockPickerBlocks" :state-path="$statePath" :width="$blockPickerWidth">
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
        @else
            <div class="fi-visual-editor__empty">
                {{ __('Contruya un sitio increible.') }}
            </div>
        @endif
        <div class="fi-visual-editor__actions">
            @if ($isAddable && $addAction->isVisible())
                <x-filament-forms::builder.block-picker :action="$addAction" :blocks="$blockPickerBlocks" :columns="['default' => 2, 'sm' => 1]"
                    :state-path="$statePath" :width="$blockPickerWidth">
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
