{{-- Block Header Component --}}
@props([
    'item',
    'uuid',
    'statePath',
    'loop',
    'isCollapsible',
    'hasBlockIcons',
    'hasBlockLabels',
    'hasBlockNumbers',
    'isBlockLabelTruncated',
    'reorderAction',
    'reorderActionIsVisible',
    'moveUpAction',
    'moveUpActionIsVisible',
    'moveDownAction',
    'moveDownActionIsVisible',
    'editAction',
    'editActionIsVisible',
    'cloneAction',
    'cloneActionIsVisible',
    'deleteAction',
    'deleteActionIsVisible',
    'visibleExtraItemActions',
    'getAction',
])

@if ($reorderActionIsVisible || $moveUpActionIsVisible || $moveDownActionIsVisible || $hasBlockIcons ||
    $hasBlockLabels || $editActionIsVisible || $cloneActionIsVisible || $deleteActionIsVisible ||
    $isCollapsible || $visibleExtraItemActions)
<div 
    @if ($isCollapsible) x-on:click.stop="isCollapsed = !isCollapsed" @endif
    class="flex overflow-hidden absolute z-10 gap-x-3 items-center px-4 py-3 w-full opacity-0 cursor-pointer select-none blockHeader group-hover:opacity-10 fi-fo-builder-item-header"
>
    @if ($reorderActionIsVisible || $moveUpActionIsVisible || $moveDownActionIsVisible)
    <ul class="flex gap-x-3 items-center">
        @if ($reorderActionIsVisible)
        <li x-sortable-handle x-on:click.stop>
            {{ $reorderAction }}
        </li>
        @endif

        @if ($moveUpActionIsVisible || $moveDownActionIsVisible)
        <li x-on:click.stop>
            {{ $moveUpAction }}
        </li>

        <li x-on:click.stop>
            {{ $moveDownAction }}
        </li>
        @endif
    </ul>
    @endif

    @php
    $blockIcon = $item->getParentComponent()->getIcon($item->getRawState(), $uuid);
    @endphp

    @if ($hasBlockIcons && filled($blockIcon))
    <x-filament::icon 
        :icon="$blockIcon"
        class="w-5 h-5 text-gray-400 fi-fo-builder-item-header-icon dark:text-gray-500" 
    />
    @endif

    @if ($hasBlockLabels)
    <h4 @class([
        'text-sm font-medium text-gray-950 dark:text-white',
        'truncate' => $isBlockLabelTruncated,
    ])>
        {{ $item->getParentComponent()->getLabel($item->getRawState(), $uuid) }}
        @if ($hasBlockNumbers)
        {{ $loop->iteration }}
        @endif

        @if(config('app.env') === 'development' || config('app.env') === 'local')
        <small 
            style="color: rgb(0, 255, 0);" 
            x-on:mouseover="$tooltip('Copiar')"
            x-on:click.stop="
                const text = '{{ $item->getParentComponent()->getName() }}';
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(text)
                        .then(() => {
                            $tooltip('Copiado!');
                        })
                        .catch(err => {
                            console.error('Error al copiar: ', err);
                        });
                } else {
                    const textArea = document.createElement('textarea');
                    textArea.value = text;
                    textArea.style.position = 'fixed';
                    textArea.style.left = '-9999px';
                    document.body.appendChild(textArea);
                    textArea.select();
                    try {
                        document.execCommand('copy');
                        $tooltip('Copiado!');
                    } catch (err) {
                        console.error('Error al copiar: ', err);
                    } finally {
                        document.body.removeChild(textArea);
                    }
                }
            ">
            {{ $item->getParentComponent()->getName() }}
        </small>
        @endif
    </h4>
    @endif

    @include('filament-forms::components.editor.block-actions', [
        'editActionIsVisible' => $editActionIsVisible,
        'editAction' => $editAction,
        'cloneActionIsVisible' => $cloneActionIsVisible,
        'cloneAction' => $cloneAction,
        'deleteActionIsVisible' => $deleteActionIsVisible,
        'deleteAction' => $deleteAction,
        'isCollapsible' => $isCollapsible,
        'visibleExtraItemActions' => $visibleExtraItemActions,
        'item' => $item,
        'uuid' => $uuid,
        'getAction' => $getAction,
    ])
</div>
@endif
