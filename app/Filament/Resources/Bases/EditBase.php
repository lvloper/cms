<?php

namespace App\Filament\Resources\Bases;

use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions;
use App\Filament\Traits\HandlesExternalImages;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;

class EditBase extends EditRecord
{
    use HandlesExternalImages;
    protected string $view = 'filament.admin.pages.edit-page';

    public ?int $selectedHistoryId = null;

    public bool $showHistoryModal = false;

    public bool $isPreviewingHistory = false;

    public ?int $previewHistoryId = null;

    public int $historyPage = 1;

    public int $historyPerPage = 25;

    public int $historyMaxRecords = 200;

    public int $historyTotalCount = 0;

    public ?int $compareHistoryId = null;

    protected ?array $historyRecordBeforeSave = null;

    protected ?array $historyRouteBeforeSave = null;

    public function getTitle(): string
    {
        $record = $this->getRecord();

        if ($record->title) {
            if ($record->route && $record->route->parent) {
                return $record->title . ' - ' . $record->route->parent->title;
            }
            return $record->title;
        }

        return 'Editar Página';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->hidden(fn() => $this->record && method_exists($this->record, 'isProtected') && $this->record->isProtected()),
        ];
    }

    public function getPageHistoryProperty(): array
    {
        if (! DB::getSchemaBuilder()->hasTable('activity_log')) {
            return [];
        }

        $query = DB::table('activity_log')
            ->where('log_name', 'Resource history')
            ->where('subject_type', $this->getRecord()::class)
            ->where('subject_id', $this->getRecord()->getKey());

        $total = $query->count();
        $this->historyTotalCount = $total;

        $loadedCount = $this->historyPage * $this->historyPerPage;

        return $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(min($loadedCount, $total))
            ->get()
            ->map(fn ($entry) => [
                'id' => $entry->id,
                'date' => $entry->created_at,
                'event' => $entry->event,
                'change' => $entry->description,
                'category' => Arr::get($this->decodeHistoryProperties($entry), 'category_label', 'Cambio'),
            ])
            ->all();
    }

    public function loadMoreHistory(): void
    {
        $this->historyPage++;
    }

    public function getHasMoreHistoryProperty(): bool
    {
        $loadedCount = $this->historyPage * $this->historyPerPage;

        return $this->historyTotalCount > $loadedCount;
    }

    public function getSelectedHistoryProperty(): ?array
    {
        if ($this->selectedHistoryId === null || ! DB::getSchemaBuilder()->hasTable('activity_log')) {
            return null;
        }

        $entry = DB::table('activity_log')
            ->where('id', $this->selectedHistoryId)
            ->where('log_name', 'Resource history')
            ->where('subject_type', $this->getRecord()::class)
            ->where('subject_id', $this->getRecord()->getKey())
            ->first();

        if (! $entry) {
            return null;
        }

        $properties = $this->decodeHistoryProperties($entry);
        $fields = Arr::get($properties, 'fields', []);

        $hasBlocks = isset($fields['blocks']);

        $result = [
            'id' => $entry->id,
            'date' => $entry->created_at,
            'event' => $entry->event,
            'change' => $entry->description,
            'category' => Arr::get($properties, 'category_label', 'Cambio'),
            'fields' => $this->formatHistoryFields($fields),
            'hasBlocksDiff' => $hasBlocks,
            'blocksDiff' => $hasBlocks ? $this->compareBlocks(
                $fields['blocks']['before'] ?? [],
                $fields['blocks']['after'] ?? []
            ) : [],
            'historyBlocksHtml' => $hasBlocks ? $this->renderBlocksPreviewHtml($fields) : [],
            'currentBlocksHtml' => $hasBlocks ? $this->renderCurrentBlocksAsHtml() : [],
        ];

        return $result;
    }

    public function openHistory(int $historyId): void
    {
        $this->selectedHistoryId = $historyId;
        $this->showHistoryModal = true;
    }

    public function closeHistoryModal(): void
    {
        $this->showHistoryModal = false;
        $this->selectedHistoryId = null;
        $this->compareHistoryId = null;
    }

    public function selectCompareHistory(int $historyId): void
    {
        $this->openHistory($historyId);
    }

    public function restoreHistory(int $historyId): void
    {
        if (! DB::getSchemaBuilder()->hasTable('activity_log')) {
            return;
        }

        $entry = DB::table('activity_log')
            ->where('id', $historyId)
            ->where('log_name', 'Resource history')
            ->where('subject_type', $this->getRecord()::class)
            ->where('subject_id', $this->getRecord()->getKey())
            ->first();

        if (! $entry) {
            Notification::make()->danger()->title('El cambio ya no existe.')->send();

            return;
        }

        $properties = $this->decodeHistoryProperties($entry);
        $record = $this->getRecord();

        DB::transaction(function () use ($properties, $record): void {
            $recordBefore = $this->snapshotRecord($record);
            $routeBefore = $this->snapshotRoute($record);
            $restoredRecord = [];
            $restoredRoute = [];

            foreach (Arr::get($properties, 'fields', []) as $field => $change) {
                $target = array_key_exists('after', $change) ? $change['after'] : ($change['before'] ?? null);

                if (str_starts_with($field, 'route.')) {
                    $restoredRoute[substr($field, 6)] = $target;

                    continue;
                }

                $restoredRecord[$field] = $target;
            }

            if ($restoredRoute !== [] && $record->route) {
                $record->route->fill($restoredRoute);
                $record->route->save();
            }

            if ($restoredRecord !== []) {
                $record->fill($restoredRecord);
                $record->save();
            }

            $record->refresh()->load('route');
            $this->writeHistoryEntry(
                $record,
                $recordBefore,
                $routeBefore,
                $this->snapshotRecord($record),
                $this->snapshotRoute($record),
                'Restauración'
            );
        });

        $this->closeHistoryModal();
        $this->fillForm();

        Notification::make()->success()->title('Cambio restaurado.')->send();
    }

    public function previewHistory(int $historyId): void
    {
        if (! DB::getSchemaBuilder()->hasTable('activity_log')) {
            return;
        }

        $entry = DB::table('activity_log')
            ->where('id', $historyId)
            ->where('log_name', 'Resource history')
            ->where('subject_type', $this->getRecord()::class)
            ->where('subject_id', $this->getRecord()->getKey())
            ->first();

        if (! $entry) {
            Notification::make()->danger()->title('El cambio ya no existe.')->send();

            return;
        }

        $properties = $this->decodeHistoryProperties($entry);
        $previewData = [];

        foreach (Arr::get($properties, 'fields', []) as $field => $change) {
            $target = array_key_exists('after', $change) ? $change['after'] : ($change['before'] ?? null);

            if (str_starts_with($field, 'route.')) {
                $previewData['route'][substr($field, 6)] = $target;

                continue;
            }

            $previewData[$field] = $target;
        }

        $this->form->fill($previewData);
        $this->closeHistoryModal();

        $this->isPreviewingHistory = true;
        $this->previewHistoryId = $historyId;

        $this->dispatch('switch-to-content-tab');

        Notification::make()
            ->info()
            ->title('Vista previa cargada.')
            ->body('Revisá los cambios en la pestaña Contenido. Si querés aplicarlos, hacé clic en "Confirmar restauración".')
            ->persistent()
            ->send();
    }

    public function confirmHistoryRestore(): void
    {
        if (! $this->previewHistoryId) {
            return;
        }

        $this->restoreHistory($this->previewHistoryId);
        $this->isPreviewingHistory = false;
        $this->previewHistoryId = null;
    }

    public function cancelHistoryPreview(): void
    {
        $this->isPreviewingHistory = false;
        $this->previewHistoryId = null;
        $this->fillForm();

        Notification::make()->info()->title('Vista previa descartada.')->send();
    }

    public function getHistoryBlocksDiff(int $historyId): array
    {
        if (! DB::getSchemaBuilder()->hasTable('activity_log')) {
            return [];
        }

        $entry = DB::table('activity_log')
            ->where('id', $historyId)
            ->where('log_name', 'Resource history')
            ->where('subject_type', $this->getRecord()::class)
            ->where('subject_id', $this->getRecord()->getKey())
            ->first();

        if (! $entry) {
            return [];
        }

        $properties = $this->decodeHistoryProperties($entry);
        $fields = Arr::get($properties, 'fields', []);

        if (! isset($fields['blocks'])) {
            return [];
        }

        $beforeBlocks = $fields['blocks']['before'] ?? [];
        $afterBlocks = $fields['blocks']['after'] ?? [];

        return $this->compareBlocks($beforeBlocks, $afterBlocks);
    }

    protected function compareBlocks(array $before, array $after): array
    {
        $result = [];
        $maxCount = max(count($before), count($after));

        for ($i = 0; $i < $maxCount; $i++) {
            $beforeBlock = $before[$i] ?? null;
            $afterBlock = $after[$i] ?? null;

            if ($beforeBlock === null && $afterBlock !== null) {
                $result[] = [
                    'index' => $i,
                    'status' => 'added',
                    'type' => $afterBlock['type'] ?? 'unknown',
                    'data' => $afterBlock['data'] ?? [],
                    'label' => $this->getBlockLabel($afterBlock['type'] ?? ''),
                ];
            } elseif ($beforeBlock !== null && $afterBlock === null) {
                $result[] = [
                    'index' => $i,
                    'status' => 'removed',
                    'type' => $beforeBlock['type'] ?? 'unknown',
                    'data' => $beforeBlock['data'] ?? [],
                    'label' => $this->getBlockLabel($beforeBlock['type'] ?? ''),
                ];
            } elseif ($beforeBlock != $afterBlock) {
                $result[] = [
                    'index' => $i,
                    'status' => 'modified',
                    'type' => $afterBlock['type'] ?? 'unknown',
                    'before' => $beforeBlock['data'] ?? [],
                    'data' => $afterBlock['data'] ?? [],
                    'label' => $this->getBlockLabel($afterBlock['type'] ?? ''),
                ];
            }
        }

        return $result;
    }

    protected function getBlockLabel(string $type): string
    {
        return str($type)->replace('_', ' ')->title()->toString();
    }

    protected function renderBlocksPreviewHtml(array $fields): array
    {
        if (! isset($fields['blocks'])) {
            return [];
        }

        $blocksAfter = $fields['blocks']['after'] ?? [];

        return $this->renderBlocksAsHtml($blocksAfter);
    }

    protected function renderCurrentBlocksAsHtml(): array
    {
        $record = $this->getRecord();
        $blocks = $record->blocks;

        if (empty($blocks)) {
            return [];
        }

        $blocksArray = json_decode(json_encode($blocks), true);

        if (! is_array($blocksArray)) {
            return [];
        }

        return $this->renderBlocksAsHtml($blocksArray);
    }

    protected function renderBlocksAsHtml(array $blocks): array
    {
        $html = [];

        foreach ($blocks as $i => $block) {
            $type = $block['type'] ?? 'unknown';
            $html[$i] = [
                'status' => 'current',
                'type' => $type,
                'label' => $this->getBlockLabel($type),
                'currentHtml' => $this->renderSingleBlockPreview($block),
            ];
        }

        return $html;
    }

    protected function renderSingleBlockPreview(array $block): string
    {
        $type = $block['type'] ?? '';
        $data = $block['data'] ?? [];
        $viewName = 'blocks.' . $type;
        $kebabName = 'blocks.' . \Illuminate\Support\Str::kebab($type);

        $viewPath = null;
        if (view()->exists($viewName)) {
            $viewPath = $viewName;
        } elseif (view()->exists($kebabName)) {
            $viewPath = $kebabName;
        }

        if (! $viewPath) {
            return '<div class="p-4 bg-gray-100 text-gray-500 text-center">Preview no disponible para: ' . e($type) . '</div>';
        }

        try {
            $uid = 'preview-' . uniqid();
            $data['id'] = $uid;
            $data['preview'] = true;

            $blockHtml = Blade::render('@include(\'' . addcslashes($viewPath, '\'') . '\', $__data)', $data, deleteCachedView: true);
            $blockHtml = str_replace('="images', '="/storage/images', $blockHtml);

            $blockHtml = '<style>.block-entrance{opacity:1!important;transform:none!important;}</style>' . $blockHtml;

            return '<div id="main">' . $blockHtml . '</div>';
        } catch (\Exception $e) {
            return '<div class="p-4 bg-red-50 text-red-600 text-center">Error: ' . e($e->getMessage()) . '</div>';
        }
    }

    public function renderBlockPreview(string $type, array $data): string
    {
        return $this->renderSingleBlockPreview(['type' => $type, 'data' => $data]);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $record = $this->getRecord();
        $record->load('route');

        $this->historyRecordBeforeSave = $this->snapshotRecord($record);
        $this->historyRouteBeforeSave = $this->snapshotRoute($record);

        // Process external image URLs and download them locally
        $data = $this->processExternalImagesInData($data);

        // Ensure image fields are always strings, not arrays
        $imageFields = ['image', 'image_mobile', 'photo', 'picture', 'avatar'];
        foreach ($imageFields as $field) {
            if (isset($data[$field]) && is_array($data[$field])) {
                $data[$field] = $data[$field][0] ?? null;
            }
        }

        // Si el modelo tiene una propiedad estática llamada 'forceParent', establece el parent_id de la ruta al valor de esa propiedad
        $routeData = $data['route'] ?? [];
        $modelClass = get_class($record);


        if (method_exists($record, 'getDefaultRouteParentId')) {
            $routeData['parent_id'] = $record->getDefaultRouteParentId();
        }
        if (method_exists($record, 'getDefaultRouteLayout')) {
            $routeData['layout'] = $record->getDefaultRouteLayout();
        }
        if ($record->route) {
            $record->route->fill($routeData);
        }

        $record->route->full_slug = $record->route->full_slug ?? $record->route->getFullPath();

        $record->route->save();

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $record->load('route');

        $recordBefore = $this->historyRecordBeforeSave ?? $this->snapshotRecord($record);
        $routeBefore = $this->historyRouteBeforeSave ?? $this->snapshotRoute($record);

        $record = parent::handleRecordUpdate($record, $data);
        $record->refresh()->load('route');

        $this->writeHistoryEntry(
            $record,
            $recordBefore,
            $routeBefore,
            $this->snapshotRecord($record),
            $this->snapshotRoute($record)
        );

        $this->historyRecordBeforeSave = null;
        $this->historyRouteBeforeSave = null;

        return $record;
    }

    protected function fillForm(): void
    {
        $record = $this->getRecord();
        $record->load('route');

        if ($record->route === null) return;

        $extraData = [
            'route' => $record->route->toArray()
        ];

        // Include all model attributes to preserve existing data like images
        $modelData = $record->toArray();

        // Process external images when loading the form
        $modelData = $this->processExternalImagesInData($modelData);

        // Persist any processed image fields back to the record so FileUpload loads from storage
        $fields = $this->getImageFieldNames();
        $updates = [];
        foreach ($fields['single'] as $field) {
            if (array_key_exists($field, $modelData) && $modelData[$field] !== $record->{$field}) {
                $updates[$field] = $modelData[$field];
            }
        }
        foreach ($fields['multiple'] as $field) {
            if (array_key_exists($field, $modelData) && $modelData[$field] !== $record->{$field}) {
                $updates[$field] = $modelData[$field];
            }
        }

        if (!empty($updates)) {
            foreach ($updates as $k => $v) {
                $record->{$k} = $v;
            }
            $record->save();
        }

        // Persist route image if it was processed
        if (isset($modelData['route']['image']) && ($record->route->image ?? null) !== $modelData['route']['image']) {
            $record->route->image = $modelData['route']['image'];
            $record->route->save();
        }

        $extraData = array_merge($modelData, $extraData);

        $this->fillFormWithDataAndCallHooks($record, $extraData);
    }

    protected function snapshotRecord(Model $record): array
    {
        $ignored = ['id', 'created_at', 'updated_at', 'deleted_at'];

        return collect($record->getAttributes())
            ->except($ignored)
            ->mapWithKeys(fn ($value, $key) => [$key => $this->normalizeHistoryValue($record->getAttribute($key))])
            ->all();
    }

    protected function snapshotRoute(Model $record): array
    {
        if (! $record->route) {
            return [];
        }

        $fields = ['title', 'slug', 'description', 'layout', 'status', 'parent_id', 'image', 'custom_css', 'header_scripts', 'footer_scripts'];

        return collect($fields)
            ->filter(fn ($field) => array_key_exists($field, $record->route->getAttributes()))
            ->mapWithKeys(fn ($field) => ["route.{$field}" => $this->normalizeHistoryValue($record->route->getAttribute($field))])
            ->all();
    }

    protected function writeHistoryEntry(
        Model $record,
        array $recordBefore,
        array $routeBefore,
        array $recordAfter,
        array $routeAfter,
        string $event = 'Actualización'
    ): void {
        if (! DB::getSchemaBuilder()->hasTable('activity_log')) {
            return;
        }

        $changes = $this->buildHistoryChanges($recordBefore + $routeBefore, $recordAfter + $routeAfter);

        if ($changes === []) {
            return;
        }

        DB::table('activity_log')->insert([
            'log_name' => 'Resource history',
            'description' => $this->summarizeHistoryChanges($changes),
            'subject_type' => $record::class,
            'subject_id' => $record->getKey(),
            'causer_type' => Auth::user()?->getMorphClass(),
            'causer_id' => Auth::id(),
            'properties' => json_encode([
                'category_label' => $this->getHistoryCategoryLabel(array_keys($changes)),
                'fields' => $changes,
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'event' => $event,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->cleanupOldHistory($record);
    }

    protected function cleanupOldHistory(Model $record): void
    {
        if (! DB::getSchemaBuilder()->hasTable('activity_log')) {
            return;
        }

        $keepIds = DB::table('activity_log')
            ->where('log_name', 'Resource history')
            ->where('subject_type', $record::class)
            ->where('subject_id', $record->getKey())
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit($this->historyMaxRecords)
            ->pluck('id');

        if ($keepIds->isEmpty()) {
            return;
        }

        DB::table('activity_log')
            ->where('log_name', 'Resource history')
            ->where('subject_type', $record::class)
            ->where('subject_id', $record->getKey())
            ->whereNotIn('id', $keepIds)
            ->delete();
    }

    protected function buildHistoryChanges(array $before, array $after): array
    {
        $changes = [];

        foreach (array_unique([...array_keys($before), ...array_keys($after)]) as $field) {
            $old = $before[$field] ?? null;
            $new = $after[$field] ?? null;

            if ($old === $new) {
                continue;
            }

            $changes[$field] = [
                'label' => $this->getHistoryFieldLabel($field),
                'before' => $old,
                'after' => $new,
            ];
        }

        return $changes;
    }

    protected function summarizeHistoryChanges(array $changes): string
    {
        $labels = collect($changes)->pluck('label')->take(3)->implode(', ');
        $extra = count($changes) > 3 ? ' y ' . (count($changes) - 3) . ' más' : '';

        return 'Cambió ' . $labels . $extra;
    }

    protected function getHistoryCategoryLabel(array $fields): string
    {
        $hasBlocks = in_array('blocks', $fields, true);
        $hasRoute = collect($fields)->contains(fn ($field) => str_starts_with($field, 'route.'));
        $hasResource = collect($fields)->contains(fn ($field) => ! str_starts_with($field, 'route.') && $field !== 'blocks');

        $categories = array_filter([
            $hasBlocks ? 'Bloques' : null,
            $hasRoute ? 'Configuración' : null,
            $hasResource ? 'Configuración del recurso' : null,
        ]);

        return implode(', ', $categories) ?: 'Cambio';
    }

    protected function getHistoryFieldLabel(string $field): string
    {
        return [
            'blocks' => 'Bloques',
            'name' => 'Nombre',
            'title' => 'Título',
            'description' => 'Descripción',
            'content' => 'Contenido',
            'image' => 'Imagen',
            'route.title' => 'Título SEO',
            'route.slug' => 'URL',
            'route.description' => 'Descripción SEO',
            'route.layout' => 'Diseño',
            'route.status' => 'Estado',
            'route.parent_id' => 'Ruta superior',
            'route.image' => 'Imagen de portada',
            'route.custom_css' => 'CSS personalizado',
            'route.header_scripts' => 'Scripts del header',
            'route.footer_scripts' => 'Scripts del footer',
        ][$field] ?? str($field)->replace('_', ' ')->title()->toString();
    }

    protected function formatHistoryFields(array $fields): array
    {
        return collect($fields)
            ->map(fn ($change, $field) => [
                'field' => $field,
                'label' => $change['label'] ?? $this->getHistoryFieldLabel($field),
                'before' => $this->formatHistoryValue($change['before'] ?? null),
                'after' => $this->formatHistoryValue($change['after'] ?? null),
                'raw_before' => $change['before'] ?? null,
                'raw_after' => $change['after'] ?? null,
            ])
            ->values()
            ->all();
    }

    protected function normalizeHistoryValue(mixed $value): mixed
    {
        if ($value instanceof \BackedEnum) {
            return $value->value;
        }

        if ($value instanceof \Illuminate\Support\Collection) {
            return $value->all();
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        return $value;
    }

    protected function formatHistoryValue(mixed $value): string
    {
        if ($value === null || $value === '') {
            return 'Sin valor';
        }

        if (is_array($value) || is_object($value)) {
            return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        return (string) $value;
    }

    protected function decodeHistoryProperties(object $entry): array
    {
        return json_decode($entry->properties ?: '[]', true) ?: [];
    }
}
