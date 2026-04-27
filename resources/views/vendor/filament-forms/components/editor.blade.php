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

    <div class="mx-auto" x-bind:style="{ 'width': cntWidth }"
        {{ $attributes->merge($getExtraAttributes(), escape: false)->class(['fi-fo-builder grid gap-y-4']) }}
        @include('filament-forms::components.editor.paste-handler-alpine', ['statePath' => $statePath])>

        @if (count($containers))
            <ul x-sortable data-sortable-animation-duration="{{ $getReorderAnimationDuration() }}"
                wire:end.stop="{{ 'mountFormComponentAction(\'' . $statePath . '\', \'reorder\', { items: $event.target.sortable.toArray() })' }}"
                class="">
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
                        x-on:expand="isCollapsed = false" x-sortable-item="{{ $uuid }}" class="blockContainer"
                        x-bind:class="{ 'fi-collapsed overflow-hidden': isCollapsed }">

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
                            'uuid' => $uuid,
                        ])
                    </li>

                    @if (!$loop->last)
                        @if ($isAddable && $addBetweenAction(['afterItem' => $uuid])->isVisible())
                            <li class="relative -top-4 !mt-0 h-0 z-10">
                                <div
                                    class="flex justify-center w-full opacity-0 transition duration-75 hover:opacity-100">
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
                            <li class="relative border-t border-gray-200 dark:border-white/10">
                                <span class="absolute -top-3 left-3 px-1 text-sm font-medium">
                                    {{ $labelBetweenItems }}
                                </span>
                            </li>
                        @endif
                    @endif
                @endforeach
            </ul>
        @else
            <div class="flex justify-center items-center h-32 text-gray-500 dark:text-gray-400">
                {{ __('Contruya un sitio increible.') }}
            </div>
        @endif
        <div class="flex justify-center gap-4 mt-4">
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
                'reorderItems' => $reorderItems,
            ])

            <div class="flex items-center ">
                @include('filament-forms::components.editor.paste-button', ['statePath' => $statePath])
            </div>
        </div>
    </div>
    @endpersist('editor')
</x-dynamic-component>
