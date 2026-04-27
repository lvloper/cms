{{-- Block Actions Component --}}
@props([
    'editActionIsVisible',
    'editAction',
    'cloneActionIsVisible',
    'cloneAction',
    'deleteActionIsVisible',
    'deleteAction',
    'isCollapsible',
    'visibleExtraItemActions',
    'item',
    'uuid',
    'getAction',
])

@if ($editActionIsVisible || $cloneActionIsVisible || $deleteActionIsVisible || $isCollapsible || $visibleExtraItemActions)
<ul class="fi-visual-editor__item-actions">
    @foreach ($visibleExtraItemActions as $extraItemAction)
    <li x-on:click.stop>
        {{ $extraItemAction(['item' => $uuid]) }}
    </li>
    @endforeach

    @if ($editActionIsVisible)
    <li x-on:click.stop>
        {{ $editAction }}
    </li>
    @endif

    @if ($cloneActionIsVisible)
    <li x-on:click.stop>
        {{ $cloneAction }}
    </li>
    @endif

    {{-- Copy button - placed after clone, before delete --}}
    @if ($cloneActionIsVisible)
    @include('filament-forms::components.editor.copy-block-button', ['item' => $item])
    @endif

    @if ($deleteActionIsVisible)
    <li x-on:click.stop>
        {{ $deleteAction }}
    </li>
    @endif

    @if ($isCollapsible)
    <li class="fi-visual-editor__collapse-action" 
        x-on:click.stop="isCollapsed = !isCollapsed"
        x-bind:style="isCollapsed ? 'transform: rotate(-180deg);' : ''">
        <div x-bind:style="isCollapsed ? 'opacity: 0; pointer-events: none;' : ''">
            {{ $getAction('collapse') }}
        </div>

        <div style="position: absolute; inset: 0; transform: rotate(180deg);"
            x-bind:style="! isCollapsed ? 'position: absolute; inset: 0; transform: rotate(180deg); opacity: 0; pointer-events: none;' : 'position: absolute; inset: 0; transform: rotate(180deg);'">
            {{ $getAction('expand') }}
        </div>
    </li>
    @endif
</ul>
@endif
