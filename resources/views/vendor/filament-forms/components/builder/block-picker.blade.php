@props([
'action',
'afterItem' => null,
'blocks',
'columns' => null,
'statePath',
'trigger',
'width' => null,
])

<x-filament::modal width="2xl" sticky-header slide-over display-classes="block" x-data="{ search: '' }" class="shadow-xl">
    <x-slot name="heading">
        <div class="mt-16">
            <x-filament::input.wrapper class="w-full">
                <x-filament::input type="text" placeholder="Buscar bloque" x-model="search" class="px-4 py-2 w-full rounded-md border" />
            </x-filament::input.wrapper>
        </div>
    </x-slot>

    <x-slot name="trigger">
        <div class="mx-auto">

            {{ $trigger }}
        </div>
    </x-slot>

    <x-filament::dropdown.list class="">
        <x-filament::grid :default="$columns['default'] ?? 1" :sm="$columns['sm'] ?? null" :md="$columns['md'] ?? null"
            :lg="$columns['lg'] ?? null" :xl="$columns['xl'] ?? null" :two-xl="$columns['2xl'] ?? null"
            direction="column" class="gap-2">
            @foreach ($blocks as $block)
            @php
            $wireClickActionArguments = ['block' => $block->getName()];

            if (filled($afterItem)) {
            $wireClickActionArguments['afterItem'] = $afterItem;
            }

            $wireClickActionArguments = \Illuminate\Support\Js::from($wireClickActionArguments);

            $wireClickAction = "mountFormComponentAction('{$statePath}', '{$action->getName()}',
            {$wireClickActionArguments})";
            @endphp
            <div x-show="search === '' || '{{ strtolower($block->getLabel()) }}'.includes(search.toLowerCase())"
                class="">
                <x-filament::dropdown.list.item x-on:click="close" :wire:click="$wireClickAction" class="rounded-md hover:bg-gray-100">
                    @if (file_exists(public_path("img/admin/blocks/{$block->getName()}.jpg")))
                    <div class="flex relative justify-center items-center h-40 bg-gray-300 opacity-55 hover:opacity-100">
                        <img class="object-contain mx-auto w-full h-full" src="{{ asset("img/admin/blocks/{$block->getName()}.jpg") }}" alt="{{ $block->getLabel() }}">
                    </div>
                    @else
                    <div class="flex relative justify-center items-center h-40 bg-gray-300 opacity-55 hover:opacity-100">
                        <span class="text-gray-600">{{ $block->getLabel() }}</span>
                    </div>
                    @endif
                </x-filament::dropdown.list.item>
            </div>
            @endforeach
        </x-filament::grid>
    </x-filament::dropdown.list>
</x-filament::modal>