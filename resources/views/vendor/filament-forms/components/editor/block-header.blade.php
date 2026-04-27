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
    class="fi-visual-editor__item-header fi-fo-builder-item-header"
>
    @if ($reorderActionIsVisible || $moveUpActionIsVisible || $moveDownActionIsVisible)
    <ul class="fi-visual-editor__item-header-list">
        @if ($reorderActionIsVisible)
        <li x-on:click.stop>
            {{ $reorderAction->extraAttributes(['x-sortable-handle' => true], merge: true) }}
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
        class="fi-visual-editor__icon fi-fo-builder-item-header-icon" 
    />
    @endif

    @if ($hasBlockLabels)
    <h4 @class([
        'fi-visual-editor__item-title',
        'truncate' => $isBlockLabelTruncated,
    ])>
        {{ $item->getParentComponent()->getLabel($item->getRawState(), $uuid) }}
        @if ($hasBlockNumbers)
        {{ $loop->iteration }}
        @endif

        @if(config('app.env') === 'development' || config('app.env') === 'local')
        <small 
            class="fi-visual-editor__item-debug"
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
