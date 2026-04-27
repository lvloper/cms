{{-- Block Preview Component --}}
@props([
    'item',
    'loop',
    'hasBlockPreviews',
    'hasInteractiveBlockPreviews',
    'editActionIsVisible',
    'statePath',
    'uuid',
])

<div x-show="! isCollapsed"
    @class([
        'fi-fo-builder-item-content relative border-t border-gray-100 dark:border-white/10',
        'p-4' => ! $hasBlockPreviews,
    ])>
    @if ($hasBlockPreviews)
    <div @class([
        'fi-fo-builder-item-preview',
        'pointer-events-none' => ! $hasInteractiveBlockPreviews,
    ])>
        @include('filament-forms::components.editor.iframe-preview', [
            'item' => $item,
            'loop' => $loop,
            'uuid' => $uuid,
        ])

        @if ($editActionIsVisible && (! $hasInteractiveBlockPreviews))
        <div class="absolute inset-0 z-[1] cursor-pointer" role="button"
            x-on:click.stop="{{ '$wire.mountFormComponentAction(\'' . $statePath . '\', \'edit\', { item: \'' . $uuid . '\' })' }}">
        </div>
        @endif
    @else
        {{ $item }}
    @endif
    </div>
</div>
