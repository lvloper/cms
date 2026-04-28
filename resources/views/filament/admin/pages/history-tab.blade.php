@php
    $history = $this->pageHistory;
    $selectedHistory = $this->selectedHistory;
@endphp

<div class="socies-history-tab">
    @if (empty($history))
        <x-filament::section>
            <div class="socies-history-empty">
                Todavía no hay cambios registrados para este recurso.
            </div>
        </x-filament::section>
    @else
        <x-filament::section>
            <div class="socies-history-table-wrap">
                <table class="socies-history-table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Tipo</th>
                            <th>Cambio realizado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($history as $entry)
                            <tr wire:click="openHistory({{ $entry['id'] }})" role="button" tabindex="0">
                                <td>{{ \Carbon\Carbon::parse($entry['date'])->format('d/m/Y H:i:s') }}</td>
                                <td><span class="socies-history-badge">{{ $entry['category'] }}</span></td>
                                <td>{{ $entry['change'] }} <span class="socies-history-event">{{ $entry['event'] }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    @endif

    <x-filament::modal
        id="socies-history-modal"
        width="7xl"
        :visible="$this->showHistoryModal"
        :close-button="false"
        :close-by-clicking-away="false"
        :close-by-escaping="false"
        sticky-header
        sticky-footer
    >
        <x-slot name="heading">
            Historial de cambios
        </x-slot>

        @if ($selectedHistory)
            <div class="socies-history-modal">
                <div class="socies-history-modal__meta">
                    <span>{{ \Carbon\Carbon::parse($selectedHistory['date'])->format('d/m/Y H:i:s') }}</span>
                    <span>{{ $selectedHistory['event'] }}</span>
                    <span>{{ $selectedHistory['category'] }}</span>
                    <span>{{ $selectedHistory['change'] }}</span>
                </div>

                <div class="socies-history-diff-list">
                    @foreach ($selectedHistory['fields'] as $field)
                        <div class="socies-history-diff">
                            <div class="socies-history-diff__title">{{ $field['label'] }}</div>
                            <div class="socies-history-diff__grid">
                                <div>
                                    <div class="socies-history-diff__header socies-history-diff__header--old">Antes</div>
                                    <pre>{{ $field['before'] }}</pre>
                                </div>
                                <div>
                                    <div class="socies-history-diff__header socies-history-diff__header--new">Después</div>
                                    <pre>{{ $field['after'] }}</pre>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <x-slot name="footer">
            <div class="socies-history-modal__actions">
                <x-filament::button color="gray" outlined wire:click="closeHistoryModal">
                    Cerrar
                </x-filament::button>

                @if ($selectedHistory)
                    <x-filament::button color="warning" wire:click="restoreHistory({{ $selectedHistory['id'] }})">
                        Restaurar cambio
                    </x-filament::button>
                @endif
            </div>
        </x-slot>
    </x-filament::modal>
</div>

@script
<script>
    $wire.watch('showHistoryModal', value => {
        window.dispatchEvent(new CustomEvent(value ? 'open-modal' : 'close-modal', {
            detail: { id: 'socies-history-modal' },
        }))
    })
</script>
@endscript

<style>
    .socies-history-table-wrap {
        overflow-x: auto;
    }

    .socies-history-table {
        width: 100%;
        border-collapse: collapse;
        font-size: .875rem;
    }

    .socies-history-table th,
    .socies-history-table td {
        padding: .75rem 1rem;
        border-bottom: 1px solid rgba(148, 163, 184, .25);
        text-align: left;
        vertical-align: top;
    }

    .socies-history-table th {
        color: rgb(100, 116, 139);
        font-weight: 600;
    }

    .socies-history-table tbody tr {
        cursor: pointer;
    }

    .socies-history-table tbody tr:hover {
        background: rgba(148, 163, 184, .12);
    }

    .socies-history-badge {
        display: inline-flex;
        border-radius: 999px;
        padding: .125rem .5rem;
        background: rgba(59, 130, 246, .12);
        color: rgb(37, 99, 235);
        font-size: .75rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .socies-history-event {
        margin-left: .375rem;
        color: rgb(100, 116, 139);
        font-size: .75rem;
    }

    .socies-history-empty {
        color: rgb(100, 116, 139);
        font-size: .875rem;
    }

    .socies-history-modal,
    .socies-history-diff-list {
        display: grid;
        gap: 1rem;
    }

    .socies-history-modal__meta {
        display: flex;
        flex-wrap: wrap;
        gap: .5rem;
        color: rgb(100, 116, 139);
        font-size: .875rem;
    }

    .socies-history-diff {
        overflow: hidden;
        border: 1px solid rgba(148, 163, 184, .3);
        border-radius: .75rem;
    }

    .socies-history-diff__title {
        padding: .75rem 1rem;
        border-bottom: 1px solid rgba(148, 163, 184, .3);
        font-weight: 700;
    }

    .socies-history-diff__grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .socies-history-diff__grid > div:first-child {
        border-right: 1px solid rgba(148, 163, 184, .3);
    }

    .socies-history-diff__header {
        padding: .5rem 1rem;
        font-size: .75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .socies-history-diff__header--old {
        background: rgba(248, 113, 113, .12);
        color: rgb(185, 28, 28);
    }

    .socies-history-diff__header--new {
        background: rgba(34, 197, 94, .12);
        color: rgb(21, 128, 61);
    }

    .socies-history-diff pre {
        min-height: 7rem;
        max-height: 28rem;
        overflow: auto;
        margin: 0;
        padding: 1rem;
        white-space: pre-wrap;
        word-break: break-word;
        font-size: .8125rem;
        line-height: 1.5;
    }

    .socies-history-modal__actions {
        display: flex;
        justify-content: flex-end;
        gap: .5rem;
        width: 100%;
    }

    .dark .socies-history-table th,
    .dark .socies-history-empty,
    .dark .socies-history-modal__meta {
        color: rgb(148, 163, 184);
    }

    @media (max-width: 768px) {
        .socies-history-diff__grid {
            grid-template-columns: 1fr;
        }

        .socies-history-diff__grid > div:first-child {
            border-right: 0;
            border-bottom: 1px solid rgba(148, 163, 184, .3);
        }
    }
</style>
