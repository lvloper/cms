<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Menu extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'items'];

    protected function casts(): array
    {
        return [
            'items' => 'json',
        ];
    }

    protected static function booted()
    {
        static::saving(function ($menu) {
            if (is_array($menu->items)) {
                $menu->items = self::normalizeItemsForStorage($menu->items);
            }
        });

        static::retrieved(function ($menu) {
            // Al recuperar el menú, asegurarse de que esté ordenado correctamente
            if (is_array($menu->items)) {
                $menu->items = self::ensureNumericOrder($menu->items);
            }
        });
    }

    private static function normalizeItemsForStorage(array $items): array
    {
        $ordered = [];
        $index = 0;

        foreach ($items as $key => $item) {
            if (! is_array($item)) {
                continue;
            }

            $token = $item['_token'] ?? null;
            if (is_string($token)) {
                $token = self::sanitizeToken($token);
            }

            if (! is_string($token) || $token === '') {
                $token = is_string($key) && $key !== '' ? $key : (string) Str::uuid();
                $token = self::sanitizeToken($token);
            }

            $item['_token'] = $token;
            $item['order'] = $index;

            if (isset($item['children']) && is_array($item['children'])) {
                $item['children'] = self::normalizeItemsForStorage($item['children']);
            } else {
                $item['children'] = [];
            }

            $ordered[$index] = $item;
            $index++;
        }

        return array_values($ordered);
    }

    private static function sanitizeToken(string $token): string
    {
        return trim((string) preg_replace('/[^A-Za-z0-9_-]/', '_', $token));
    }

    private static function ensureNumericOrder(array $items): array
    {
        $ordered = array_values(array_filter($items, fn ($item) => is_array($item)));

        foreach ($ordered as &$item) {
            if (isset($item['children']) && is_array($item['children'])) {
                $item['children'] = self::ensureNumericOrder($item['children']);
            } else {
                $item['children'] = [];
            }
        }

        return $ordered;
    }
}
