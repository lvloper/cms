<?php

namespace App\Filament\Resources\MenuResource\Pages;

use App\Filament\Resources\MenuResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Str;

class EditMenu extends EditRecord
{
    protected static string $resource = MenuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Restaurar la estructura con tokens como claves para el AdjacencyList
        if (isset($data['items']) && is_array($data['items'])) {
            $data['items'] = $this->restoreTokenStructure($data['items']);
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['items']) && is_array($data['items'])) {
            $data['items'] = $this->normalizeItemsForSave($data['items']);
        }

        return $data;
    }

    private function restoreTokenStructure(array $items): array
    {
        $restored = [];

        foreach ($items as $item) {
            $item = $this->unwrapSerializedArray($item);

            if (! is_array($item)) {
                continue;
            }

            $token = isset($item['_token']) && is_string($item['_token'])
                ? $this->sanitizeToken($item['_token'])
                : '';

            if ($token === '') {
                $token = $this->generateSafeToken();
            }

            while (array_key_exists($token, $restored)) {
                $token = $this->generateSafeToken();
            }

            // Remover el token del array del item
            $cleanItem = $item;
            unset($cleanItem['_token']);
            unset($cleanItem['order']);

            // Si tiene hijos, procesarlos recursivamente
            if (isset($cleanItem['children']) && is_array($cleanItem['children'])) {
                $cleanItem['children'] = $this->restoreTokenStructure($cleanItem['children']);
            } else {
                $cleanItem['children'] = [];
            }

            $restored[(string) $token] = $cleanItem;
        }

        return $restored;
    }

    private function sanitizeToken(string $token): string
    {
        return trim((string) preg_replace('/[^A-Za-z0-9_-]/', '_', $token));
    }

    private function generateSafeToken(): string
    {
        return 'menu_' . Str::lower((string) Str::ulid());
    }

    private function normalizeItemsForSave(array $items): array
    {
        $normalized = [];

        foreach ($items as $item) {
            $item = $this->unwrapSerializedArray($item);

            if (! is_array($item)) {
                continue;
            }

            $children = [];
            if (isset($item['children']) && is_array($item['children'])) {
                $children = $this->normalizeItemsForSave($item['children']);
            }

            $token = isset($item['_token']) && is_string($item['_token'])
                ? $this->sanitizeToken($item['_token'])
                : '';

            if ($token === '') {
                $token = $this->generateSafeToken();
            }

            $cleanItem = $item;
            $cleanItem['children'] = $children;
            $cleanItem['_token'] = $token;
            unset($cleanItem['order']);

            $normalized[] = $cleanItem;
        }

        return array_values($normalized);
    }

    private function unwrapSerializedArray(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        if (
            array_is_list($value)
            && count($value) === 2
            && is_array($value[1])
            && (($value[1]['s'] ?? null) === 'arr')
        ) {
            return $value[0] ?? [];
        }

        return $value;
    }
}
