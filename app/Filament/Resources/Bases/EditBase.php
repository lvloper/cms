<?php

namespace App\Filament\Resources\Bases;

use Filament\Resources\Pages\EditRecord;
use Filament\Actions;
use App\Filament\Traits\HandlesExternalImages;

class EditBase extends EditRecord
{
    use HandlesExternalImages;
    protected static string $view = 'filament.admin.pages.edit-page';

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
            Actions\DeleteAction::make(),
        ];
    }    
    
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $record = $this->getRecord();

        // Process external image URLs and download them locally
        $data = $this->processExternalImagesInData($data);

        // Ensure image fields are always strings, not arrays
        $imageFields = ['image', 'image_mobile', 'photo', 'picture', 'avatar'];
        foreach ($imageFields as $field) {
            if (isset($data[$field]) && is_array($data[$field])) {
                $data[$field] = $data[$field][0] ?? null;
            }
        }
        
        // Convert TipTap editor fields from arrays to JSON strings
        $tiptapFields = ['description', 'content', 'body'];
        foreach ($tiptapFields as $field) {
            if (isset($data[$field]) && is_array($data[$field])) {
                $data[$field] = json_encode($data[$field]);
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
}