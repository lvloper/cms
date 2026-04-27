@props(['statePath', 'reorderItems'])

@php
    $hasItems = !empty($reorderItems);
    $modalId = 'reorder-modal-' . str_replace('.', '-', $statePath);
    $itemsJson = collect($reorderItems)->map(fn($item) => ['id' => $item['id'], 'label' => $item['label']])->values()->toJson();
@endphp

<div
    class="inline-block"
    x-data="{
        items: {{ $itemsJson }},
        dragging: null,
        dropping: null,

        openModal() {
            document.getElementById('{{ $modalId }}').showModal();
        },

        dragstart(item) {
            this.dragging = item;
        },

        dragover(item) {
            if (this.dragging === item) return;
            this.dropping = item;
        },

        dragleave() {
            this.dropping = null;
        },

        drop(item) {
            if (this.dragging === item) return;

            const fromIndex = this.items.indexOf(this.dragging);
            const toIndex = this.items.indexOf(item);

            // Remover de posición original
            this.items.splice(fromIndex, 1);
            // Insertar en nueva posición
            this.items.splice(toIndex, 0, this.dragging);

            this.dropping = null;
        },

        dragend() {
            this.dragging = null;
            this.dropping = null;
        },

        applyOrder() {
            const ids = this.items.map(item => item.id);
            console.log('Reordering with IDs:', ids);
            $wire.mountFormComponentAction('{{ $statePath }}', 'reorder', { items: ids });
            this.closeModal();
        },

        closeModal() {
            document.getElementById('{{ $modalId }}').close();
        }
    }"
>
    <button
        type="button"
        class="fi-btn fi-btn-color-gray fi-btn-size-sm gap-1.5 px-3 py-2 text-sm inline-grid grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 rounded-lg shadow-sm bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 focus:ring-2 focus:ring-primary-500 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-700"
        x-on:click="openModal()"
    >
        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M2.24 6.8a.75.75 0 001.06-.04l1.95-2.1v8.59a.75.75 0 001.5 0V4.66l1.95 2.1a.75.75 0 101.1-1.02l-3.25-3.5a.75.75 0 00-1.1 0L2.2 5.74a.75.75 0 00.04 1.06zm8 6.4a.75.75 0 00-.04 1.06l3.25 3.5a.75.75 0 001.1 0l3.25-3.5a.75.75 0 10-1.1-1.02l-1.95 2.1V6.75a.75.75 0 00-1.5 0v8.59l-1.95-2.1a.75.75 0 00-1.06-.04z" clip-rule="evenodd" />
        </svg>
        <span>{{ __('Reordenar') }}</span>
    </button>

    <dialog
        id="{{ $modalId }}"
        class="p-0 rounded-xl shadow-xl backdrop:bg-gray-950/50 dark:backdrop:bg-gray-950/75 max-w-md w-full bg-white dark:bg-gray-900"
        x-on:click.self="closeModal()"
    >
        <div class="flex flex-col">
            <div class="flex items-start justify-between p-4 border-b border-gray-200 dark:border-white/10">
                <div>
                    <h3 class="text-lg font-semibold text-gray-950 dark:text-white">
                        {{ __('Reordenar bloques') }}
                    </h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {{ __('Arrastra para cambiar el orden de los bloques.') }}
                    </p>
                </div>
                <button
                    type="button"
                    x-on:click="closeModal()"
                    class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300"
                >
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                    </svg>
                </button>
            </div>

            <div class="p-4 max-h-96 overflow-y-auto">
                <ul class="space-y-2">
                    <template x-for="(item, index) in items" :key="item.id">
                        <li
                            draggable="true"
                            x-on:dragstart="dragstart(item)"
                            x-on:dragover.prevent="dragover(item)"
                            x-on:dragleave="dragleave()"
                            x-on:drop.prevent="drop(item)"
                            x-on:dragend="dragend()"
                            :class="{
                                'opacity-50 scale-95': dragging === item,
                                'border-primary-500 border-2': dropping === item
                            }"
                            class="flex items-center gap-3 px-3 py-2 bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-white/10 cursor-grab active:cursor-grabbing select-none transition-all duration-150"
                        >
                            <span class="flex items-center justify-center w-8 h-8 rounded-md text-gray-400">
                                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M2 4.75A.75.75 0 012.75 4h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 4.75zm0 5A.75.75 0 012.75 9h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 9.75zm0 5a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75a.75.75 0 01-.75-.75z" clip-rule="evenodd" />
                                </svg>
                            </span>
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-200 flex-1 truncate" x-text="item.label"></span>
                        </li>
                    </template>
                </ul>
            </div>

            <div class="flex justify-end gap-3 p-4 border-t border-gray-200 dark:border-white/10">
                <button
                    type="button"
                    class="fi-btn fi-btn-color-gray fi-btn-size-sm gap-1.5 px-3 py-2 text-sm inline-grid grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 rounded-lg shadow-sm bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-700"
                    x-on:click="closeModal()"
                >
                    {{ __('Cancelar') }}
                </button>

                <button
                    type="button"
                    class="fi-btn fi-btn-color-primary fi-btn-size-sm gap-1.5 px-3 py-2 text-sm inline-grid grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 rounded-lg shadow-sm bg-primary-600 text-white hover:bg-primary-500 focus:ring-2 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed"
                    x-on:click="applyOrder()"
                    x-bind:disabled="items.length === 0"
                >
                    {{ __('Aplicar orden') }}
                </button>
            </div>
        </div>
    </dialog>
</div>
