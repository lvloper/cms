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
<ul class="flex gap-x-3 items-center ms-auto">
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
    <li class="relative transition" 
        x-on:click.stop="isCollapsed = !isCollapsed"
        x-bind:class="{ '-rotate-180': isCollapsed }">
        <div class="transition" x-bind:class="{ 'opacity-0 pointer-events-none': isCollapsed }">
            {{ $getAction('collapse') }}
        </div>

        <div class="absolute inset-0 transition rotate-180"
            x-bind:class="{ 'opacity-0 pointer-events-none': ! isCollapsed }">
            {{ $getAction('expand') }}
        </div>
    </li>
    @endif
</ul>
@endif
