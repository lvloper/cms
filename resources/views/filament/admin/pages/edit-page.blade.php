<x-filament-panels::page
    @class([
        'fi-resource-edit-record-page',
        'fi-resource-' . str_replace('/', '-', $this->getResource()::getSlug()),
        'fi-resource-record-' . $record->getKey(),
    ])
>

    @isset($record->route)

        <div>
    
            <div class="flex gap-4 -mt-6" >
                <x-filament::button 
                    tooltip="Editar"
                    x-on:click="focusSlug()"
                    size="xs"
                    class="p-0"
                    color="transparent"
                >
                    <span class="-ml-2 text-gray-500">{{ $record->url }}</span> 
                </x-filament::button>
            
                
                <x-filament::button 
                    tag="a"
                    href="{{ $record->url }}"
                    target="_blank"
                     size="xs"
                    class="p-0"
                    tooltip="Ver página"
                    color="transparent"
                    >
                    <x-heroicon-o-link class="w-3" />
                </x-filament::button >

                <x-filament::button 
                    tooltip="Vista previa"
                     size="xs"
                    tag="a"
                    href="{{ $record->preview_url }}"
                    target="_blank"
                    class="p-0"
                    color="transparent"
                >
                    <x-heroicon-o-eye class="w-3" />
                </x-filament::button >
            </div>
        </div>
        
        <script>
            function focusSlug() {
                setTimeout(() => {
                    document.querySelector('[x-ref="tabsData"]').parentNode.querySelector('[role="tab"]:nth-child(2)').click();
                }, 100);
                setTimeout(() => {
                    document.querySelector('[wire\\:model\\.live="data.route.slug"]').focus();
                }, 200);
            }
        </script>
    @endisset


    @capture($form)
        <x-filament-panels::form
            id="form"
            :wire:key="$this->getId() . '.forms.' . $this->getFormStatePath()"
            wire:submit="save"
        >
            {{ $this->form }}

            <x-filament-panels::form.actions
                :actions="$this->getCachedFormActions()"
                :full-width="$this->hasFullWidthFormActions()"
            />
        </x-filament-panels::form>

        <style>
            .fi-form-actions {
                position: sticky;
                bottom: 0;
                margin: -5px;
                padding: .5rem;
                width: 100%;
                background: #09090b;
            }
        </style>
    @endcapture

    @php
        $relationManagers = $this->getRelationManagers();
        $hasCombinedRelationManagerTabsWithContent = $this->hasCombinedRelationManagerTabsWithContent();
    @endphp

    @if ((! $hasCombinedRelationManagerTabsWithContent) || (! count($relationManagers)))
        {{ $form() }}
    @endif

    @if (count($relationManagers))
        <x-filament-panels::resources.relation-managers
            :active-locale="isset($activeLocale) ? $activeLocale : null"
            :active-manager="$this->activeRelationManager ?? ($hasCombinedRelationManagerTabsWithContent ? null : array_key_first($relationManagers))"
            :content-tab-label="$this->getContentTabLabel()"
            :content-tab-icon="$this->getContentTabIcon()"
            :content-tab-position="$this->getContentTabPosition()"
            :managers="$relationManagers"
            :owner-record="$record"
            :page-class="static::class"
        >
            @if ($hasCombinedRelationManagerTabsWithContent)
                <x-slot name="content">
                    {{ $form() }}
                </x-slot>
            @endif
        </x-filament-panels::resources.relation-managers>
    @endif

    <x-filament-panels::page.unsaved-data-changes-alert />
</x-filament-panels::page>