{{-- Block Preview Component --}}
@props([
    'item',
    'loop',
    'hasBlockPreviews',
    'hasInteractiveBlockPreviews',
    'editActionIsVisible',
    'statePath',
    'key',
    'uuid',
])

<div x-show="! isCollapsed"
    @class([
        'fi-fo-builder-item-content fi-visual-editor__item-content',
        'p-4' => ! $hasBlockPreviews,
    ])>
    @if ($hasBlockPreviews)
    <div @class([
        'fi-fo-builder-item-preview fi-visual-editor__preview',
        'pointer-events-none' => ! $hasInteractiveBlockPreviews,
    ])>
        @include('filament-forms::components.editor.iframe-preview', [
            'item' => $item,
            'loop' => $loop,
            'uuid' => $uuid,
        ])

        @if ($editActionIsVisible && (! $hasInteractiveBlockPreviews))
        <div class="fi-visual-editor__preview-overlay pointer-events-auto" role="button"
            x-on:dblclick.stop="$wire.mountAction('edit', { item: '{{ $uuid }}' }, { schemaComponent: '{{ $key }}' })">
        </div>
        @endif
    @else
        {{ $item }}
    @endif
    </div>
</div>
