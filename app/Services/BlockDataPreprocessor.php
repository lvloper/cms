<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class BlockDataPreprocessor
{
    protected LinkResolver $linkResolver;

    protected array $imageFields = [
        'Media' => ['image'],
        'MediaText' => ['image'],
        'Cards' => ['items' => ['image']],
        'ClientProjects' => ['projects' => ['media_image']],
        'ClientFeature' => ['media' => ['media_image']],
        'ClientStatement' => ['media_image'],
        'ClientMetrics' => ['media_image'],
        'ClientTestimonial' => ['testimonials' => ['media_image']],
        'ClientClosing' => ['media' => ['media_image']],
    ];

    protected array $routeFields = [
        'MediaText' => ['cta'],
        'Cards' => ['items' => ['route']],
        'ClientClosing' => ['cta'],
    ];

    protected array $storageFields = [
        'Media' => ['video_file'],
        'MediaText' => ['video_file'],
        'ClientProjects' => ['projects' => ['media_video']],
        'ClientFeature' => ['media' => ['media_video']],
        'ClientStatement' => ['media_video'],
        'ClientMetrics' => ['media_video'],
        'ClientTestimonial' => ['testimonials' => ['media_video']],
        'ClientClosing' => ['media' => ['media_video']],
    ];

    public function __construct(?LinkResolver $linkResolver = null)
    {
        $this->linkResolver = $linkResolver ?? new LinkResolver;
    }

    public function process(array $blocks): array
    {
        return array_map(function (array $block): array {
            if ($block['data']['hidden'] ?? false) {
                return $block;
            }

            $block['data'] = $this->processBlockData(
                $block['type'],
                $block['data']
            );

            return $block;
        }, $blocks);
    }

    protected function processBlockData(string $type, array $data): array
    {
        $imageFields = $this->imageFields[$type] ?? [];
        $routeFields = $this->routeFields[$type] ?? [];
        $storageFields = $this->storageFields[$type] ?? [];

        foreach ($data as $key => &$value) {
            if (is_null($value)) {
                continue;
            }

            if ($this->isImageField($key, $imageFields)) {
                $value = $this->resolveImage($value);
            } elseif ($this->isRouteField($key, $routeFields)) {
                $value = $this->linkResolver->resolve((array) $value);
            } elseif ($this->isStorageField($key, $storageFields)) {
                $value = $this->resolveStorage($value);
            } elseif ($this->isRepeaterField($key, $imageFields, $routeFields, $storageFields)) {
                $value = $this->processRepeater($key, $value, $imageFields, $routeFields, $storageFields);
            }
        }

        return $data;
    }

    protected function resolveImage(mixed $value): mixed
    {
        if (is_string($value)) {
            return str_starts_with($value, 'http') ? $value : Storage::url($value);
        }

        if (is_array($value)) {
            return array_map(fn ($img): string => is_string($img) && ! str_starts_with($img, 'http')
                ? Storage::url($img)
                : $img, $value);
        }

        return $value;
    }

    protected function resolveStorage(mixed $value): mixed
    {
        if (is_string($value) && ! str_starts_with($value, 'http')) {
            return Storage::url($value);
        }

        return $value;
    }

    protected function isImageField(string $key, array $imageFields): bool
    {
        return in_array($key, $imageFields);
    }

    protected function isRouteField(string $key, array $routeFields): bool
    {
        return in_array($key, $routeFields);
    }

    protected function isStorageField(string $key, array $storageFields): bool
    {
        return in_array($key, $storageFields);
    }

    protected function isRepeaterField(string $key, array $imageFields, array $routeFields, array $storageFields): bool
    {
        return isset($imageFields[$key]) || isset($routeFields[$key]) || isset($storageFields[$key]);
    }

    protected function processRepeater(string $key, mixed $value, array $imageFields, array $routeFields, array $storageFields): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        $subImageFields = (array) ($imageFields[$key] ?? []);
        $subRouteFields = (array) ($routeFields[$key] ?? []);
        $subStorageFields = (array) ($storageFields[$key] ?? []);

        return array_map(function (array $item) use ($subImageFields, $subRouteFields, $subStorageFields): array {
            foreach ($item as $field => &$val) {
                if (in_array($field, $subImageFields)) {
                    $val = $this->resolveImage($val);
                } elseif (in_array($field, $subRouteFields)) {
                    $val = $this->linkResolver->resolve((array) $val);
                } elseif (in_array($field, $subStorageFields)) {
                    $val = $this->resolveStorage($val);
                }
            }

            return $item;
        }, $value);
    }
}
