<?php

namespace App\Services;

use App\Models\Route;
use Illuminate\Support\Facades\Storage;

class LinkResolver
{
    public function resolve(?array $attrs): array
    {
        if (!$attrs) {
            return $this->empty();
        }

        $url = '#';
        $isModal = false;
        $isFile = false;
        $downloadName = null;
        $newWindow = $attrs['new_window'] ?? false;
        $anchor = $attrs['anchor'] ?? null;
        $label = $attrs['btn_label'] ?? null;
        $routeId = $attrs['route_id'] ?? null;

        if ($routeId && is_numeric($routeId) && (int) $routeId >= 1) {
            $route = Route::find((int) $routeId);
            if ($route) {
                $url = url($route->full_slug);
                $isModal = ($route->layout ?? 'default') === 'modal' && !$newWindow;
            }
        }

        if ($routeId === '0' && ($attrs['external_url'] ?? null)) {
            $url = $attrs['external_url'];
            $newWindow = $attrs['new_window'] ?? true;
        }

        if ($routeId === '-1' || $routeId === -1) {
            $isFile = true;
            $file = $attrs['file'] ?? null;

            if ($file) {
                if (is_string($file)) {
                    $url = Storage::url($file);
                    $downloadName = $attrs['download_name'] ?? basename($file);
                } elseif (is_array($file)) {
                    $url = isset($file['path']) ? Storage::url($file['path']) : (isset($file['url']) ? $file['url'] : '#');
                    $downloadName = $attrs['download_name'] ?? $file['name'] ?? ($file['path'] ?? null ? basename($file['path']) : null);
                }
            }

            if (!$downloadName && isset($attrs['download_name'])) {
                $downloadName = $attrs['download_name'];
            }
        }

        if ($anchor && !$isModal) {
            $url .= '#' . $anchor;
        }

        return [
            'url' => $url,
            'is_modal' => $isModal,
            'is_file' => $isFile,
            'download_name' => $downloadName,
            'new_window' => $newWindow,
            'label' => $label,
            'route_id' => $routeId,
        ];
    }

    public function empty(): array
    {
        return [
            'url' => null,
            'is_modal' => false,
            'is_file' => false,
            'download_name' => null,
            'new_window' => false,
            'label' => null,
            'route_id' => null,
        ];
    }
}
