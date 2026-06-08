@php
    $history = $this->pageHistory;
    $selectedHistory = $this->selectedHistory;
    $isPreviewing = $this->isPreviewingHistory;
@endphp

<div class="socies-history-tab">
    @if ($isPreviewing)
        <x-filament::section>
            <div class="socies-history-preview-banner">
                <div class="socies-history-preview-banner__icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="24" height="24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/>
                    </svg>
                </div>
                <div class="socies-history-preview-banner__content">
                    <strong>Vista previa activa</strong>
                    <p>Estás viendo los bloques del historial. Revisá los cambios en la pestaña "Contenido".</p>
                </div>
                <div class="socies-history-preview-banner__actions">
                    <x-filament::button color="gray" size="sm" wire:click="cancelHistoryPreview">
                        Descartar
                    </x-filament::button>
                    <x-filament::button color="success" size="sm" wire:click="confirmHistoryRestore">
                        Confirmar restauración
                    </x-filament::button>
                </div>
            </div>
        </x-filament::section>
    @endif

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

            @if ($this->hasMoreHistory)
                <div class="socies-history-load-more">
                    <x-filament::button color="gray" outlined wire:click="loadMoreHistory">
                        Ver más ({{ $this->historyTotalCount - ($this->historyPage * $this->historyPerPage) }} restantes)
                    </x-filament::button>
                </div>
            @endif
        </x-filament::section>
    @endif

    <x-filament::modal
        id="socies-history-modal"
        width="full"
        :visible="$this->showHistoryModal"
        :close-button="false"
        :close-by-clicking-away="false"
        :close-by-escaping="false"
        sticky-header
        sticky-footer
    >
        <x-slot name="heading">
            <div class="socies-history-modal-heading">
                <span>Comparar versiones</span>
                @if ($selectedHistory)
                    <span class="socies-history-modal-heading__date">{{ \Carbon\Carbon::parse($selectedHistory['date'])->format('d/m/Y H:i:s') }}</span>
                @endif
            </div>
        </x-slot>

        @if ($selectedHistory)
            <div class="socies-history-modal" x-data="{ showJson: false }">
                <div class="socies-history-modal__meta">
                    <span>Evento: {{ $selectedHistory['event'] }}</span>
                    <span>Categoría: {{ $selectedHistory['category'] }}</span>
                    <span>{{ $selectedHistory['change'] }}</span>
                </div>

                @if ($selectedHistory['hasBlocksDiff'])
                    <div class="socies-history-blocks-section">
                        <div class="socies-history-blocks-header">
                            <h3>
                                Bloques
                                <span class="socies-history-blocks-count">{{ count($selectedHistory['historyBlocksHtml']) }} en historial vs {{ count($selectedHistory['currentBlocksHtml']) }} actuales</span>
                            </h3>
                            <div class="socies-history-header-actions">
                                @if (!empty($history))
                                <select wire:change="selectCompareHistory($event.target.value)" class="socies-history-version-select">
                                    <option value="">Comparar con otra versión...</option>
                                    @foreach ($history as $hEntry)
                                        <option value="{{ $hEntry['id'] }}" {{ $selectedHistory['id'] == $hEntry['id'] ? 'selected' : '' }}>
                                            {{ \Carbon\Carbon::parse($hEntry['date'])->format('d/m/Y H:i') }} — {{ $hEntry['change'] }}
                                        </option>
                                    @endforeach
                                </select>
                                @endif
                                <button type="button" @click="showJson = !showJson" class="socies-history-toggle-json">
                                    <span x-show="!showJson">JSON</span>
                                    <span x-show="showJson">Preview</span>
                                </button>
                            </div>
                        </div>

                        <div x-show="!showJson" class="socies-history-blocks-previews socies-history-blocks-previews--compare">
                            @php
                                $maxBlocks = max(count($selectedHistory['historyBlocksHtml']), count($selectedHistory['currentBlocksHtml']));
                            @endphp

                            <div class="socies-history-compare-labels">
                                <div class="socies-history-compare-label socies-history-compare-label--old">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                    {{ $selectedHistory['event'] === 'Restauración' ? 'Restaurado' : 'Antes' }}
                                    <span class="socies-history-compare-label__date">{{ \Carbon\Carbon::parse($selectedHistory['date'])->format('d/m/Y H:i') }}</span>
                                </div>
                                <div class="socies-history-compare-label socies-history-compare-label--new">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                    Ahora
                                    <span class="socies-history-compare-label__date">Estado actual</span>
                                </div>
                            </div>

                            @for ($i = 0; $i < $maxBlocks; $i++)
                                @php
                                    $historyBlock = $selectedHistory['historyBlocksHtml'][$i] ?? null;
                                    $currentBlock = $selectedHistory['currentBlocksHtml'][$i] ?? null;
                                    $blockUid = 'hp-' . uniqid();
                                @endphp
                                <div class="socies-history-block-pair">
                                    <div class="socies-history-block-pair__header">
                                        <span class="socies-history-block-pair__number">#{{ $i + 1 }}</span>
                                        <span class="socies-history-block-pair__label">{{ $historyBlock['label'] ?? $currentBlock['label'] ?? '' }}</span>
                                        @if ($historyBlock && $currentBlock && $historyBlock['currentHtml'] !== $currentBlock['currentHtml'])
                                            <span class="socies-history-badge socies-history-badge--diff">Modificado</span>
                                        @elseif ($historyBlock && !$currentBlock)
                                            <span class="socies-history-badge socies-history-badge--removed">Eliminado</span>
                                        @elseif (!$historyBlock && $currentBlock)
                                            <span class="socies-history-badge socies-history-badge--added">Agregado</span>
                                        @endif
                                    </div>
                                    <div class="socies-history-block-pair__grid">
                                        <div class="socies-history-block-pair__col">
                                            @if ($historyBlock)
                                                <div class="socies-history-block-preview__render"
                                                     x-data="{
                                                         loading: true,
                                                         hostId: '{{ $blockUid }}-history',
                                                         contentId: '{{ $blockUid }}-history-src',
                                                         instance: null,
                                                         init() {
                                                             const ready = () => {
                                                                 if (!window.blockPreviewManager) return setTimeout(ready, 50);
                                                                 this.instance = window.blockPreviewManager.createInstance(this.hostId, this.contentId, this);
                                                                 if (!this.instance.init()) { setTimeout(ready, 100); }
                                                             };
                                                             ready();
                                                         }
                                                     }">
                                                    <div id="{{ $blockUid }}-history" x-cloak x-show="!loading" class="block-preview-shadow-host" style="width: 100%; display: block; background: #fff; min-height: 200px;"></div>
                                                    <div hidden id="{{ $blockUid }}-history-src">{!! $historyBlock['currentHtml'] !!}</div>
                                                </div>
                                            @else
                                                <div class="socies-history-block-pair__empty">Sin bloque</div>
                                            @endif
                                        </div>
                                        <div class="socies-history-block-pair__col">
                                            @if ($currentBlock)
                                                <div class="socies-history-block-preview__render"
                                                     x-data="{
                                                         loading: true,
                                                         hostId: '{{ $blockUid }}-current',
                                                         contentId: '{{ $blockUid }}-current-src',
                                                         instance: null,
                                                         init() {
                                                             const ready = () => {
                                                                 if (!window.blockPreviewManager) return setTimeout(ready, 50);
                                                                 this.instance = window.blockPreviewManager.createInstance(this.hostId, this.contentId, this);
                                                                 if (!this.instance.init()) { setTimeout(ready, 100); }
                                                             };
                                                             ready();
                                                         }
                                                     }">
                                                    <div id="{{ $blockUid }}-current" x-cloak x-show="!loading" class="block-preview-shadow-host" style="width: 100%; display: block; background: #fff; min-height: 200px;"></div>
                                                    <div hidden id="{{ $blockUid }}-current-src">{!! $currentBlock['currentHtml'] !!}</div>
                                                </div>
                                            @else
                                                <div class="socies-history-block-pair__empty">Sin bloque</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endfor
                        </div>

                        <div x-show="showJson" x-cloak class="socies-history-diff-list">
                            @foreach ($selectedHistory['fields'] as $field)
                                @if ($field['field'] === 'blocks')
                                    <div class="socies-history-diff">
                                        <div class="socies-history-diff__title">{{ $field['label'] }}</div>
                                        <div class="socies-history-diff__grid">
                                            <div>
                                                <div class="socies-history-diff__header socies-history-diff__header--old">Antes (historial)</div>
                                                <pre>{{ $field['before'] }}</pre>
                                            </div>
                                            <div>
                                                <div class="socies-history-diff__header socies-history-diff__header--new">Después (historial)</div>
                                                <pre>{{ $field['after'] }}</pre>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="socies-history-diff-list">
                    @foreach ($selectedHistory['fields'] as $field)
                        @if ($field['field'] !== 'blocks')
                            <div class="socies-history-diff">
                                <div class="socies-history-diff__title">{{ $field['label'] }}</div>
                                <div class="socies-history-diff__grid">
                                    <div>
                                        <div class="socies-history-diff__header socies-history-diff__header--old">Antes</div>
                                        <pre>{{ $field['before'] }}</pre>
                                    </div>
                                    <div>
                                        <div class="socies-history-diff__header socies-history-diff__header--new">Ahora</div>
                                        <pre>{{ $field['after'] }}</pre>
                                    </div>
                                </div>
                            </div>
                        @endif
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
                    <x-filament::button color="warning" wire:click="previewHistory({{ $selectedHistory['id'] }})">
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

    $wire.on('switch-to-content-tab', () => {
        setTimeout(() => {
            const tabsComponent = document.querySelector('[x-data*="tabsSchemaComponent"]');
            if (tabsComponent && tabsComponent.__x) {
                const tabsData = tabsComponent.__x.$data;
                const tabs = tabsData.getTabs();
                if (tabs.includes('Contenido')) {
                    tabsData.tab = 'Contenido';
                }
            }
        }, 100);
    });
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

    .socies-history-badge--diff {
        background: rgba(251, 191, 36, .12);
        color: rgb(161, 98, 7);
    }

    .socies-history-badge--removed {
        background: rgba(248, 113, 113, .12);
        color: rgb(185, 28, 28);
    }

    .socies-history-badge--added {
        background: rgba(34, 197, 94, .12);
        color: rgb(21, 128, 61);
    }

    .socies-history-modal-heading {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .socies-history-modal-heading__date {
        font-size: .875rem;
        font-weight: 400;
        color: rgb(100, 116, 139);
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

    .socies-history-load-more {
        display: flex;
        justify-content: center;
        padding: 1rem 0 0;
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

    .socies-history-preview-banner {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        background: rgba(59, 130, 246, .08);
        border: 1px solid rgba(59, 130, 246, .2);
        border-radius: .75rem;
    }

    .socies-history-preview-banner__icon {
        flex-shrink: 0;
        color: rgb(59, 130, 246);
    }

    .socies-history-preview-banner__content {
        flex: 1;
    }

    .socies-history-preview-banner__content strong {
        display: block;
        color: rgb(30, 64, 175);
        margin-bottom: .25rem;
    }

    .socies-history-preview-banner__content p {
        margin: 0;
        color: rgb(59, 130, 246);
        font-size: .875rem;
    }

    .socies-history-preview-banner__actions {
        display: flex;
        gap: .5rem;
        flex-shrink: 0;
    }

    .socies-history-blocks-section {
        margin-bottom: 1.5rem;
    }

    .socies-history-blocks-count {
        font-size: .8125rem;
        font-weight: 400;
        color: rgb(100, 116, 139);
        margin-left: .5rem;
    }

    .socies-history-header-actions {
        display: flex;
        align-items: center;
        gap: .5rem;
    }

    .socies-history-version-select {
        padding: .375rem .75rem;
        background: rgba(100, 116, 139, .08);
        border: 1px solid rgba(100, 116, 139, .2);
        border-radius: .5rem;
        color: rgb(71, 85, 105);
        font-size: .8125rem;
        font-weight: 500;
        cursor: pointer;
        transition: all .15s ease;
        max-width: 280px;
    }

    .socies-history-version-select:hover {
        border-color: rgba(100, 116, 139, .4);
    }

    .socies-history-version-select:focus {
        outline: none;
        border-color: rgb(59, 130, 246);
        box-shadow: 0 0 0 2px rgba(59, 130, 246, .2);
    }

    .socies-history-blocks-previews--compare {
        display: grid;
        gap: 1.5rem;
    }

    .socies-history-compare-labels {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .socies-history-compare-label {
        display: flex;
        align-items: center;
        gap: .5rem;
        padding: .75rem 1rem;
        border-radius: .5rem;
        font-weight: 700;
        font-size: .875rem;
    }

    .socies-history-compare-label--old {
        background: rgba(248, 113, 113, .1);
        color: rgb(185, 28, 28);
    }

    .socies-history-compare-label--new {
        background: rgba(34, 197, 94, .1);
        color: rgb(21, 128, 61);
    }

    .socies-history-compare-label__date {
        font-weight: 400;
        font-size: .75rem;
        opacity: .8;
        margin-left: auto;
    }

    .socies-history-block-pair {
        border: 1px solid rgba(148, 163, 184, .25);
        border-radius: .75rem;
        overflow: hidden;
    }

    .socies-history-block-pair__header {
        display: flex;
        align-items: center;
        gap: .5rem;
        padding: .5rem 1rem;
        background: rgba(248, 250, 252, .5);
        border-bottom: 1px solid rgba(148, 163, 184, .2);
    }

    .socies-history-block-pair__number {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 1.5rem;
        height: 1.5rem;
        border-radius: 999px;
        background: rgba(100, 116, 139, .12);
        color: rgb(71, 85, 105);
        font-size: .75rem;
        font-weight: 700;
    }

    .socies-history-block-pair__label {
        font-weight: 600;
        color: rgb(30, 41, 59);
        font-size: .875rem;
    }

    .socies-history-block-pair__grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0;
        min-height: 0;
    }

    .socies-history-block-pair__col {
        min-width: 0;
        border-right: 1px solid rgba(148, 163, 184, .15);
    }

    .socies-history-block-pair__col:last-child {
        border-right: none;
    }

    .socies-history-block-pair__empty {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 120px;
        color: rgb(148, 163, 184);
        font-size: .875rem;
        font-style: italic;
    }

    .socies-history-blocks-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        padding-bottom: .75rem;
        border-bottom: 2px solid rgba(59, 130, 246, .2);
        flex-wrap: wrap;
        gap: .5rem;
    }

    .socies-history-blocks-header h3 {
        margin: 0;
        font-size: 1.125rem;
        font-weight: 700;
        color: rgb(30, 64, 175);
    }

    .socies-history-toggle-json {
        padding: .5rem 1rem;
        background: rgba(100, 116, 139, .1);
        border: 1px solid rgba(100, 116, 139, .2);
        border-radius: .5rem;
        color: rgb(71, 85, 105);
        font-size: .875rem;
        font-weight: 600;
        cursor: pointer;
        transition: all .15s ease;
    }

    .socies-history-toggle-json:hover {
        background: rgba(100, 116, 139, .15);
        border-color: rgba(100, 116, 139, .3);
    }

    .socies-history-blocks-previews {
        display: grid;
        gap: 1rem;
    }

    .socies-history-block-preview {
        border: 1px solid rgba(148, 163, 184, .3);
        border-radius: .75rem;
        overflow: hidden;
    }

    .socies-history-block-preview__header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: .75rem 1rem;
        background: rgba(248, 250, 252, .5);
        border-bottom: 1px solid rgba(148, 163, 184, .3);
    }

    .socies-history-block-preview__label {
        font-weight: 700;
        color: rgb(30, 41, 59);
    }

    .socies-history-block-preview__status {
        display: inline-flex;
        padding: .25rem .75rem;
        border-radius: 999px;
        font-size: .75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .socies-history-block-preview__status--added {
        background: rgba(34, 197, 94, .12);
        color: rgb(21, 128, 61);
    }

    .socies-history-block-preview__status--removed {
        background: rgba(248, 113, 113, .12);
        color: rgb(185, 28, 28);
    }

    .socies-history-block-preview__status--modified {
        background: rgba(251, 191, 36, .12);
        color: rgb(161, 98, 7);
    }

    .socies-history-block-preview__content {
        padding: 1rem;
    }

    .socies-history-block-preview__compare {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
    }

    .socies-history-block-preview__compare-col {
        border: 1px solid rgba(148, 163, 184, .3);
        border-radius: .5rem;
        overflow: hidden;
    }

    .socies-history-block-preview__render {
        background: #fff;
        min-height: 200px;
        max-height: 500px;
        overflow: auto;
        position: relative;
    }

    .block-preview-shadow-host {
        width: 100%;
        display: block;
        background: #fff;
        min-height: 200px;
    }

    .dark .socies-history-preview-banner {
        background: rgba(59, 130, 246, .15);
        border-color: rgba(59, 130, 246, .3);
    }

    .dark .socies-history-preview-banner__content strong {
        color: rgb(147, 197, 253);
    }

    .dark .socies-history-preview-banner__content p {
        color: rgb(96, 165, 250);
    }

    .dark .socies-history-modal-heading__date {
        color: rgb(148, 163, 184);
    }

    .dark .socies-history-blocks-count {
        color: rgb(148, 163, 184);
    }

    .dark .socies-history-version-select {
        background: rgb(30, 41, 59);
        border-color: rgba(148, 163, 184, .3);
        color: rgb(226, 232, 240);
    }

    .dark .socies-history-version-select:hover {
        border-color: rgba(148, 163, 184, .5);
    }

    .dark .socies-history-version-select:focus {
        border-color: rgb(96, 165, 250);
        box-shadow: 0 0 0 2px rgba(96, 165, 250, .2);
    }

    .dark .socies-history-version-select option {
        background: rgb(30, 41, 59);
        color: rgb(226, 232, 240);
    }

    .dark .socies-history-compare-label--old {
        background: rgba(248, 113, 113, .18);
        color: rgb(252, 165, 165);
    }

    .dark .socies-history-compare-label--new {
        background: rgba(34, 197, 94, .18);
        color: rgb(134, 239, 172);
    }

    .dark .socies-history-block-pair__header {
        background: rgba(30, 41, 59, .5);
    }

    .dark .socies-history-block-pair__number {
        background: rgba(148, 163, 184, .2);
        color: rgb(203, 213, 225);
    }

    .dark .socies-history-block-pair__label {
        color: rgb(226, 232, 240);
    }

    .dark .socies-history-block-pair__empty {
        color: rgb(71, 85, 105);
    }

    .dark .socies-history-blocks-header h3 {
        color: rgb(147, 197, 253);
    }

    .dark .socies-history-toggle-json {
        background: rgba(148, 163, 184, .15);
        border-color: rgba(148, 163, 184, .3);
        color: rgb(203, 213, 225);
    }

    .dark .socies-history-block-preview__label {
        color: rgb(226, 232, 240);
    }

    .dark .socies-history-block-preview__render {
        background: rgb(30, 41, 59);
    }

    @media (max-width: 768px) {
        .socies-history-preview-banner {
            flex-direction: column;
            align-items: flex-start;
        }

        .socies-history-preview-banner__actions {
            width: 100%;
        }

        .socies-history-preview-banner__actions button {
            flex: 1;
        }

        .socies-history-block-preview__compare {
            grid-template-columns: 1fr;
        }
    }
</style>
