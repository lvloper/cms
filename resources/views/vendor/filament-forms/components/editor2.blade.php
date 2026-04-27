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

@endphp

<x-dynamic-component class="fi-visual-editor" :component="$getFieldWrapperView()" :field="$field" x-data="
    {
        currentDevice: 'desktop',
    }
">

    <div class="flex justify-center">
        {{-- @if ($collapseAllActionIsVisible || $expandAllActionIsVisible)
        <div @class([ 'flex gap-x-3' , 'hidden'=> count($containers) < 2, ])>
                <span x-init="console.dir($data)"></span>
                <span x-if="isCollapsed==true" x-on:click="$dispatch('builder-collapse', '{{ $statePath }}')">
                    <x-filament::icon icon="heroicon-o-arrows-pointing-in"
                        class="w-5 h-5 text-gray-400 fi-fo-builder-item-header-icon dark:text-gray-500" />
                </span>


                <span x-else x-on:click="$dispatch('builder-expand', '{{ $statePath }}')">
                    <x-filament::icon icon="heroicon-o-arrows-pointing-out"
                        class="w-5 h-5 text-gray-400 fi-fo-builder-item-header-icon dark:text-gray-500" />
                </span>
        </div>
        @endif --}}


    </div>

    @persist('editor')

    <div class="mx-auto w-full"  x-data="{}" {{ $attributes ->merge($getExtraAttributes(), escape: false)
        ->class(['fi-fo-builder grid gap-y-4'])
        }}
        >


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
            fn (Action $action): bool => $action(['item' => $uuid])->isVisible(),
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

            <li wire:key="{{ $this->getId() }}.{{ $item->getStatePath() }}.{{ $field::class }}.item" x-data="{
                            isCollapsed: @js($isCollapsed($item)),
                        }" x-on:builder-expand.window="$event.detail === '{{ $statePath }}' && (isCollapsed = false)"
                x-on:builder-collapse.window="$event.detail === '{{ $statePath }}' && (isCollapsed = true)"
                x-on:expand="isCollapsed = false" x-sortable-item="{{ $uuid }}" class="blockContainer"
                x-bind:class="{ 'fi-collapsed overflow-hidden': isCollapsed }">
                @if ($reorderActionIsVisible || $moveUpActionIsVisible || $moveDownActionIsVisible || $hasBlockIcons ||
                $hasBlockLabels || $editActionIsVisible || $cloneActionIsVisible || $deleteActionIsVisible ||
                $isCollapsible || $visibleExtraItemActions)
                <div @if ($isCollapsible) x-on:click.stop="isCollapsed = !isCollapsed" @endif
                    class="flex overflow-hidden absolute z-10 gap-x-3 items-center px-4 py-3 w-full opacity-0 cursor-pointer select-none blockHeader group-hover:opacity-10 fi-fo-builder-item-header">
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
                    <x-filament::icon :icon="$blockIcon"
                        class="w-5 h-5 text-gray-400 fi-fo-builder-item-header-icon dark:text-gray-500" />
                    @endif

                    @if ($hasBlockLabels)
                    <h4 @class([ 'text-sm font-medium text-gray-950 dark:text-white' , 'truncate'=>
                        $isBlockLabelTruncated(),
                        ])
                        >
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

                    @if ($editActionIsVisible || $cloneActionIsVisible || $deleteActionIsVisible || $isCollapsible ||
                    $visibleExtraItemActions)
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

                        @if ($deleteActionIsVisible)
                        <li x-on:click.stop>
                            {{ $deleteAction }}
                        </li>
                        @endif

                        @if ($isCollapsible)
                        <li class="relative transition" x-on:click.stop="isCollapsed = !isCollapsed"
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
                </div>
                @endif

                <div x-show="! isCollapsed"
                    @class([ 'fi-fo-builder-item-content relative border-t border-gray-100 dark:border-white/10'
                    , 'p-4 '=> ! $hasBlockPreviews,
                    ])
                    >
                    @if ($hasBlockPreviews)
                    <div @class([ 'fi-fo-builder-item-preview' , 'pointer-events-none'=> ! $hasInteractiveBlockPreviews,
                        ])
                        >
                        <div class="block isolate relative" x-on:mouseover="matchHeight();"
                                x-ref="blockPreviewer"
                                x-data="{ 
                                loading: true,
                                changeIframeContent() { 
                                    const MAIN = $refs.iframePreview.contentDocument?.querySelector('#main');
                                    if (MAIN) {
                                        MAIN.innerHTML = $refs.renderSlot.innerHTML;
                                        if ($refs.iframePreview.contentWindow?.Alpine) {
                                            $refs.iframePreview.contentWindow.Alpine.start();
                                            $refs.iframePreview.contentWindow.Alpine.initTree(MAIN);
                                        }
                                    }
                                },
                                matchHeight() {
                                    const el = $refs.iframePreview.contentDocument?.querySelector('#main .block-preview');
                                    if (el) {
                                        const mb = parseInt(window.getComputedStyle(el).getPropertyValue('margin-bottom'));
                                        const h = el.offsetHeight + mb;
                                        $refs.iframePreview.style.height = '100%';
                                    }
                                }
                            }" x-init="
                                $refs.iframePreview.onload = () => {
                                    $data.loading = false;
                                    changeIframeContent();
                                    matchHeight();
                                };

                                $wire.$watch('data', () => {
                                    setTimeout(() => { changeIframeContent(); matchHeight(); }, 10);
                                });

                                $refs.iframePreview.contentWindow?.addEventListener('resize', () => {
                                    matchHeight();
                                });

                                $refs.iframePreview.contentDocument?.querySelector('#main')?.addEventListener('resize', () => {
                                    matchHeight();
                                });
                            ">

                            <div class="block bg-gray-100 pulse-opacity dark:bg-gray-800" x-show="loading"
                                style="height: 80px" x-ref="skeleton"></div>
                            <iframe
                                id="iframe{{ $loop->iteration }}"
                            x-cloak x-show="!loading" x-ref="iframePreview" src="{{ route('preview.blocks') }}"
                                frameborder="0" style="width: 100%; height: 80px; overflow: hidden;"></iframe>


                            <div class="hidden" x-ref="renderSlot">
                                @php
                                $block = $item->getRawState();
                                $uid = uniqid();

                                $hidden = $block['hidden'] ?? false;
                                $mb = $block['mb'] ?? "";
                                $mdMb = $block['mdMb'] ?? "";
                                $clases = $block['clases'] ?? [];
                                $styles = $block['styles'] ?? [];
                                $stylesMd = $block['stylesMd'] ?? [];
                                $allClasses = implode(' ', array_merge([$mb, $mdMb], $clases));



                                $styleString = '';

                                if ($styles) {
                                $styleString .= '<style>
                                    ';
 foreach ($styles as $key => $value) {
                                        $styleString .="#b{$uid} { {$key}: {$value}; } ";
                                    }

                                    $styleString .='
                                </style>';
                                }

                                if ($stylesMd) {
                                $styleString .= '<style>
                                    @media (min-width: 768px) {
                                        ';
 foreach ($stylesMd as $key => $value) {
                                            $styleString .="#b{$uid} { {$key}: {$value}; } ";
                                        }

                                        $styleString .='} 
                                </style>';
                                }

                                @endphp
                                <div id="b{{$uid}}" class="block block-preview relative {{ $allClasses }}">

                                    @if ($hidden)
                                    <div class="block-hidden">

                                        <span class="block-hidden-text">{{ __('Este bloque se encuentra oculto')
                                            }}</span>
                                    </div>

                                    @endif
                                    @php
                                        $data = $item->getRawState();
                                        $data['id'] = 'block-'.$uid;
                                        // dd($data);
                                    @endphp
                                    {{ $item->getParentComponent()->renderPreview($data) }}

                                    {!! $styleString !!}

                                    <div class="clear-both"></div>
                                </div>
                            </div>

                        </div>

                        @if ($editActionIsVisible && (! $hasInteractiveBlockPreviews))
                        <div class="absolute inset-0 z-[1] cursor-pointer" role="button"
                            x-on:click.stop="{{ '$wire.mountFormComponentAction(\'' . $statePath . '\', \'edit\', { item: \'' . $uuid . '\' })' }}">
                        </div>
                        @endif
                        @else
                        {{ $item }}
                        @endif
                    </div>
            </li>

            @if (! $loop->last)
            @if ($isAddable && $addBetweenAction(['afterItem' => $uuid])->isVisible())
            <li class="relative -top-4 !mt-0 h-0 z-10">
                <div class="flex justify-center w-full opacity-0 transition duration-75 hover:opacity-100">
                    <div class="bg-white rounded-lg fi-fo-builder-block-picker-ctn dark:bg-gray-900">
                        <x-filament-forms::builder.block-picker :action="$addBetweenAction" :after-item="$uuid"
                            :columns="$blockPickerColumns" :blocks="$blockPickerBlocks" :state-path="$statePath"
                            :width="$blockPickerWidth">
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


        @if ($isAddable && $addAction->isVisible())
        <x-filament-forms::builder.block-picker :action="$addAction" :blocks="$blockPickerBlocks"
            :columns="[
                'default' => 2,
                'sm' => 1,
            ]" :state-path="$statePath" :width="$blockPickerWidth"
            class="flex justify-center">
            <x-slot name="trigger">
                {{ $addAction }}
            </x-slot>
        </x-filament-forms::builder.block-picker>
        @endif
    </div>
    @endpersist('editor')
</x-dynamic-component>