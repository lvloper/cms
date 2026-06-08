@props(['statePath', 'key', 'reorderItems'])

@php
    $hasItems = !empty($reorderItems);
    $modalId = 'reorder-modal-' . str_replace('.', '-', $statePath);
    $itemsJson = collect($reorderItems)->map(fn($item) => ['id' => $item['id'], 'label' => $item['label']])->values()->toJson();
@endphp

<div
    style="display: inline-block;"
    x-data="{
        items: {{ $itemsJson }},
        sortedIds: null,

        openModal() {
            this.sortedIds = null;
            document.getElementById('{{ $modalId }}').showModal();
        },

        applyOrder() {
            const ids = this.sortedIds || this.items.map(item => item.id);
            console.log('Reordering with IDs:', ids);
            $wire.mountAction('reorder', { items: ids }, { schemaComponent: '{{ $key }}' });
            this.closeModal();
        },

        closeModal() {
            document.getElementById('{{ $modalId }}').close();
        }
    }"
>
    <button
        type="button"
        class="fi-btn fi-btn-color-gray fi-btn-size-sm"
        x-on:click="openModal()"
    >
        <svg class="fi-visual-editor__icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M2.24 6.8a.75.75 0 001.06-.04l1.95-2.1v8.59a.75.75 0 001.5 0V4.66l1.95 2.1a.75.75 0 101.1-1.02l-3.25-3.5a.75.75 0 00-1.1 0L2.2 5.74a.75.75 0 00.04 1.06zm8 6.4a.75.75 0 00-.04 1.06l3.25 3.5a.75.75 0 001.1 0l3.25-3.5a.75.75 0 10-1.1-1.02l-1.95 2.1V6.75a.75.75 0 00-1.5 0v8.59l-1.95-2.1a.75.75 0 00-1.06-.04z" clip-rule="evenodd" />
        </svg>
        <span>{{ __('Reordenar') }}</span>
    </button>

    <dialog
        id="{{ $modalId }}"
        class="fi-visual-editor__reorder-modal"
        x-on:click.self="closeModal()"
    >
        <div>
            <div class="fi-visual-editor__reorder-header">
                <div>
                    <h3 class="fi-visual-editor__reorder-title">
                        {{ __('Reordenar bloques') }}
                    </h3>
                    <p class="fi-visual-editor__reorder-description">
                        {{ __('Arrastra para cambiar el orden de los bloques.') }}
                    </p>
                </div>
                <button
                    type="button"
                    x-on:click="closeModal()"
                    class="fi-visual-editor__close-button"
                >
                    <svg class="fi-visual-editor__icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                    </svg>
                </button>
            </div>

            <div class="fi-visual-editor__reorder-body">
                <template x-if="items && items.length">
                    <ul
                        class="fi-visual-editor__reorder-list"
                        x-sortable
                        data-sortable-animation-duration="150"
                        x-on:end.stop="sortedIds = $event.target.sortable.toArray()"
                    >
                        <template x-for="(item, index) in items" :key="item.id">
                            <li
                                class="fi-visual-editor__reorder-item"
                                x-bind:x-sortable-item="item.id"
                            >
                                <span class="fi-visual-editor__reorder-handle" x-sortable-handle>
                                    <svg class="fi-visual-editor__icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M2 4.75A.75.75 0 012.75 4h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 4.75zm0 5A.75.75 0 012.75 9h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 9.75zm0 5a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75a.75.75 0 01-.75-.75z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                                <span class="fi-visual-editor__reorder-label" x-text="item.label"></span>
                            </li>
                        </template>
                    </ul>
                </template>
            </div>

            <div class="fi-visual-editor__reorder-footer">
                <button
                    type="button"
                    class="fi-btn fi-btn-color-gray fi-btn-size-sm"
                    x-on:click="closeModal()"
                >
                    {{ __('Cancelar') }}
                </button>

                <button
                    type="button"
                    class="fi-btn fi-btn-color-primary fi-btn-size-sm"
                    x-on:click="applyOrder()"
                    x-bind:disabled="!items || items.length === 0"
                >
                    {{ __('Aplicar orden') }}
                </button>
            </div>
        </div>
    </dialog>
</div>
