@php
    use Filament\Support\Enums\Alignment;
    use Filament\Support\Enums\GridDirection;
    use Illuminate\View\ComponentAttributeBag;
@endphp

@props([
    'action',
    'actionAlignment' => null,
    'afterItem' => null,
    'blocks',
    'columns' => null,
    'key' => null,
    'trigger',
    'width' => null,
])

@php
    $blockLabels = collect($blocks)->map(fn ($block) => $block->getLabel())->values();
    $blockCategoryList = collect($blocks)->map(fn ($block) => $block->getMeta('category', 'Otros'))->values()->all();
    $uniqueCategories = collect($blockCategoryList)->unique()->values()->all();
    $pickerTitleId = 'fi-builder-block-picker-title-' . uniqid();
@endphp

@once
    <style>
        .fi-builder-block-picker-modal {
            position: fixed;
            inset: 0;
            z-index: 999;
            display: flex;
            justify-content: flex-end;
            background: rgb(15 23 42 / 0.35);
        }

        .fi-builder-block-picker-modal__panel {
            display: grid;
            grid-template-rows: auto auto 1fr;
            width: min(100%, 34rem);
            height: 100%;
            background: white;
            color: var(--gray-950);
            border-left: 1px solid var(--gray-200);
        }

        .dark .fi-builder-block-picker-modal__panel {
            background: var(--gray-900);
            color: white;
            border-left-color: rgb(255 255 255 / 0.1);
        }

        .fi-builder-block-picker-modal__header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            padding: 1.25rem;
            border-bottom: 1px solid var(--gray-200);
        }

        .dark .fi-builder-block-picker-modal__header {
            border-bottom-color: rgb(255 255 255 / 0.1);
        }

        .fi-builder-block-picker-modal__title {
            margin: 0;
            font-size: 1rem;
            font-weight: 600;
            line-height: 1.5;
        }

        .fi-builder-block-picker-modal__description {
            margin: 0.25rem 0 0;
            color: var(--gray-500);
            font-size: 0.875rem;
            line-height: 1.5;
        }

        .dark .fi-builder-block-picker-modal__description {
            color: var(--gray-400);
        }

        .fi-builder-block-picker-modal__close {
            display: grid;
            flex: 0 0 auto;
            place-items: center;
            width: 2rem;
            height: 2rem;
            color: var(--gray-500);
            background: transparent;
            border: 0;
            border-radius: 999px;
            cursor: pointer;
        }

        .fi-builder-block-picker-modal__close:hover {
            background: var(--gray-100);
            color: var(--gray-700);
        }

        .dark .fi-builder-block-picker-modal__close:hover {
            background: rgb(255 255 255 / 0.08);
            color: var(--gray-200);
        }

        .fi-builder-block-picker-modal__categories {
            display: flex;
            flex-wrap: wrap;
            align-items: stretch;
            gap: 0.375rem;
            padding: 0 0 0.75rem;
        }

        .fi-builder-block-picker-modal__categories .fi-badge {
            min-height: 2.125rem;
        }

        .fi-builder-block-picker-modal__search {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--gray-200);
        }

        .dark .fi-builder-block-picker-modal__search {
            border-bottom-color: rgb(255 255 255 / 0.1);
        }

        .fi-builder-block-picker-modal__search-input {
            width: 100%;
            min-height: 2.5rem;
            padding: 0.5rem 0.875rem;
            color: var(--gray-950);
            background: white;
            border: 1px solid var(--gray-300);
            border-radius: 0.75rem;
            font-size: 0.875rem;
            line-height: 1.5;
        }

        .fi-builder-block-picker-modal__search-input:focus {
            border-color: var(--primary-500);
            outline: 2px solid color-mix(in srgb, var(--primary-500) 25%, transparent);
            outline-offset: 0;
        }

        .dark .fi-builder-block-picker-modal__search-input {
            color: white;
            background: var(--gray-950);
            border-color: rgb(255 255 255 / 0.12);
        }

        .fi-builder-block-picker-modal__body {
            overflow-y: auto;
            padding: 1rem;
        }

        .fi-builder-block-picker-modal__list {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.75rem;
        }

        .fi-builder-block-picker-modal__item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            width: 100%;
            min-height: 4.75rem;
            padding: 0.625rem;
            color: var(--gray-800);
            text-align: left;
            background: var(--gray-50);
            border: 1px solid var(--gray-200);
            border-radius: 0.75rem;
            cursor: pointer;
        }

        .fi-builder-block-picker-modal__item:hover,
        .fi-builder-block-picker-modal__item:focus-visible {
            background: color-mix(in srgb, var(--primary-500) 8%, transparent);
            border-color: var(--primary-500);
            outline: 0;
        }

        .dark .fi-builder-block-picker-modal__item {
            color: var(--gray-100);
            background: var(--gray-800);
            border-color: rgb(255 255 255 / 0.1);
        }

        .dark .fi-builder-block-picker-modal__item:hover,
        .dark .fi-builder-block-picker-modal__item:focus-visible {
            background: color-mix(in srgb, var(--primary-500) 16%, transparent);
            border-color: color-mix(in srgb, var(--primary-500) 70%, white);
        }

        .fi-builder-block-picker-modal__preview {
            flex: 0 0 auto;
            width: 5rem;
            height: 3rem;
            object-fit: cover;
            border-radius: 0.5rem;
        }

        .fi-builder-block-picker-modal__label {
            min-width: 0;
            font-size: 0.875rem;
            font-weight: 600;
            line-height: 1.35;
        }

        .fi-builder-block-picker-modal__empty {
            padding: 2rem 1rem;
            color: var(--gray-500);
            font-size: 0.875rem;
            text-align: center;
        }

        .fi-builder-block-picker-preview {
            flex: 1 1 auto;
            width: 50rem;
            max-width: 60vw;
            min-width: 30rem;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            background: rgb(255 255 255 / 0.35);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border-right: 1px solid var(--gray-200);
            pointer-events: none;
        }

        .dark .fi-builder-block-picker-preview {
            background: rgb(15 23 42 / 0.4);
            border-right-color: rgb(255 255 255 / 0.1);
        }

        .fi-builder-block-picker-preview * {
            pointer-events: none;
        }

        .fi-builder-block-picker-preview__image {
            display: block;
            max-width: 100%;
            max-height: 85vh;
            object-fit: contain;
            border-radius: 0.75rem;
            box-shadow: 0 8px 40px rgba(0, 0, 0, 0.2);
        }

        @media (max-width: 1024px) {
            .fi-builder-block-picker-preview {
                display: none;
            }
        }

        @media (max-width: 640px) {
            .fi-builder-block-picker-modal__panel {
                width: 100%;
            }

            .fi-builder-block-picker-modal__list {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endonce

<div
    x-data="{
        open: false,
        hoveredPreview: null,
        search: '',
        selectedCategory: 'all',
        blockLabels: @js($blockLabels),
        blockCategoryList: @js($blockCategoryList),
        uniqueCategories: @js($uniqueCategories),
        matches(label) {
            return label.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '').includes(
                this.search.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '')
            )
        },
        hasVisibleBlocks() {
            return this.blockLabels.some((label, i) =>
                this.matches(label) &&
                (this.selectedCategory === 'all' || this.blockCategoryList[i] === this.selectedCategory)
            )
        },
        openPicker() {
            this.open = true
            this.$nextTick(() => this.$refs.searchInput?.focus())
        },
        closePicker() {
            this.open = false
            this.search = ''
            this.selectedCategory = 'all'
        },
    }"
    x-on:keydown.escape.window="open && closePicker()"
    {{
        \Filament\Support\prepare_inherited_attributes(
            $attributes->class([
                'fi-fo-builder-block-picker',
                ($actionAlignment instanceof Alignment) ? ('fi-align-' . $actionAlignment->value) : $actionAlignment => $actionAlignment,
            ]),
        )
    }}
>
    <div x-on:click.prevent.stop="openPicker()">
        {{ $trigger }}
    </div>

    <template x-teleport="body">
        <div
            x-cloak
            x-show="open"
            class="fi-builder-block-picker-modal"
            role="dialog"
            aria-modal="true"
            aria-labelledby="{{ $pickerTitleId }}"
            x-on:click.self="closePicker()"
        >
            <div
                x-show="hoveredPreview"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 -translate-x-4"
                x-transition:enter-end="opacity-100 translate-x-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-x-0"
                x-transition:leave-end="opacity-0 -translate-x-4"
                class="fi-builder-block-picker-preview"
            >
                <img :src="hoveredPreview" alt="" class="fi-builder-block-picker-preview__image" />
            </div>

            <div class="fi-builder-block-picker-modal__panel">
                <header class="fi-builder-block-picker-modal__header">
                    <div>
                        <h2 id="{{ $pickerTitleId }}" class="fi-builder-block-picker-modal__title">
                            {{ __('Añadir bloque') }}
                        </h2>
                        <p class="fi-builder-block-picker-modal__description">
                            {{ __('Busca y selecciona el bloque que querés agregar a la página.') }}
                        </p>
                    </div>

                    <button type="button" class="fi-builder-block-picker-modal__close" x-on:click="closePicker()" aria-label="{{ __('Cerrar') }}">
                        <x-filament::icon icon="heroicon-m-x-mark" class="h-5 w-5" />
                    </button>
                </header>

                <div class="fi-builder-block-picker-modal__search">
                    <input
                        x-ref="searchInput"
                        x-model="search"
                        type="search"
                        class="fi-builder-block-picker-modal__search-input"
                        placeholder="{{ __('Buscar bloque') }}"
                        autocomplete="off"
                    />
                </div>

                <div class="fi-builder-block-picker-modal__body">
                    <div
                        class="fi-builder-block-picker-modal__categories"
                        x-show="uniqueCategories.length > 1"
                    >
                        <x-filament::badge
                            tag="button"
                            color="gray"
                            x-on:click="selectedCategory = 'all'"
                            x-bind:class="selectedCategory === 'all' ? 'fi-color fi-color-primary' : ''"
                        >
                            {{ __('Todas') }}
                        </x-filament::badge>

                        <template x-for="cat in uniqueCategories" :key="cat">
                            <x-filament::badge
                                tag="button"
                                color="gray"
                                x-on:click="selectedCategory = cat"
                                x-bind:class="selectedCategory === cat ? 'fi-color fi-color-primary' : ''"
                            >
                                <span x-text="cat"></span>
                            </x-filament::badge>
                        </template>
                    </div>

                    <div class="fi-builder-block-picker-modal__list">
                        @foreach ($blocks as $block)
                            @php
                                $blockIcon = $block->getIcon();
                                $blockName = $block->getName();
                                $blockLabel = $block->getLabel();
                                $blockCategory = $block->getMeta('category', 'Otros');
                                $previewPath = collect(['png', 'jpg', 'jpeg', 'webp'])
                                    ->map(fn (string $extension) => "img/admin/blocks/{$blockName}.{$extension}")
                                    ->first(fn (string $path) => file_exists(public_path($path)));
                                $wireClickActionArguments = ['block' => $block->getName()];

                                if (filled($afterItem)) {
                                    $wireClickActionArguments['afterItem'] = $afterItem;
                                }

                                $wireClickActionArguments = \Illuminate\Support\Js::from($wireClickActionArguments);
                                $schemaComponentKey = $key ?? '';
                                $wireClickAction = "mountAction('{$action->getName()}', {$wireClickActionArguments}, { schemaComponent: '{$schemaComponentKey}' })";
                            @endphp

                            <button
                                type="button"
                                class="fi-builder-block-picker-modal__item"
                                x-show="matches(@js($blockLabel)) && (selectedCategory === 'all' || @js($blockCategory) === selectedCategory)"
                                x-on:click="closePicker()"
                                wire:click="{{ $wireClickAction }}"
                                @if ($previewPath)
                                    x-on:mouseenter="hoveredPreview = '{{ asset($previewPath) }}'"
                                    x-on:mouseleave="hoveredPreview = null"
                                @endif
                            >
                                @if ($previewPath)
                                    <img
                                        src="{{ asset($previewPath) }}"
                                        alt="Vista previa de {{ $blockLabel }}"
                                        class="fi-builder-block-picker-modal__preview"
                                    >
                                @elseif ($blockIcon)
                                    <x-filament::icon :icon="$blockIcon" class="h-6 w-6 text-gray-500 dark:text-gray-400" />
                                @endif

                                <span class="fi-builder-block-picker-modal__label">{{ $blockLabel }}</span>
                            </button>
                        @endforeach
                    </div>

                    <p class="fi-builder-block-picker-modal__empty" x-cloak x-show="! hasVisibleBlocks()">
                        {{ __('No se encontraron bloques con ese nombre.') }}
                    </p>
                </div>
            </div>
        </div>
    </template>
</div>
