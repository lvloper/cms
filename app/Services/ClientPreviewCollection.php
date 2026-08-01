<?php

namespace App\Services;

use App\Enums\Status;
use App\Models\Client;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ClientPreviewCollection
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function featured(): array
    {
        return Client::query()
            ->whereNotNull('logo')
            ->where('is_featured', true)
            ->whereHas('route', fn (Builder $query) => $query->where('status', Status::Published))
            ->with('route')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(function (Client $client): array {
                $title = $client->route?->title ?? 'Cliente';

                return [
                    'id' => $client->id,
                    'src' => Storage::url($client->logo),
                    'url' => url($client->route?->getFullPath() ?? '#'),
                    'alt' => $title,
                    'title' => $title,
                    'color' => $client->color,
                    'popupTextColor' => $client->popup_text_color,
                    'testimonial' => $this->testimonial($client->testimonials?->first()),
                    'previewItems' => $this->previewItems($client),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>|null  $testimonial
     * @return array{text: string, person: string, position: string}|null
     */
    private function testimonial(?array $testimonial): ?array
    {
        if (! $testimonial) {
            return null;
        }

        $text = Str::of(strip_tags((string) ($testimonial['testimonial'] ?? '')))
            ->squish()
            ->toString();

        if ($text === '') {
            return null;
        }

        return [
            'text' => $text,
            'person' => (string) ($testimonial['person'] ?? ''),
            'position' => (string) ($testimonial['position'] ?? ''),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function previewItems(Client $client): array
    {
        $videoExtensions = ['mp4', 'webm', 'mov'];
        $imageExtensions = ['jpg', 'jpeg', 'png', 'webp', 'avif'];
        $testimonial = $this->testimonial($client->testimonials?->first());

        return collect($client->preview_items)
            ->filter(fn (mixed $item): bool => is_array($item))
            ->map(function (array $item, int $index) use ($imageExtensions, $testimonial, $videoExtensions): ?array {
                if (($item['type'] ?? null) === 'testimonial') {
                    return $testimonial ? [
                        'id' => "preview-{$index}",
                        'type' => 'testimonial',
                        'content' => $testimonial,
                        'durationMs' => $this->previewDuration($item),
                    ] : null;
                }

                if (blank($item['file'] ?? null)) {
                    return null;
                }

                $file = (string) $item['file'];
                $extension = Str::lower(pathinfo($file, PATHINFO_EXTENSION));

                if (! in_array($extension, [...$imageExtensions, ...$videoExtensions], true)) {
                    return null;
                }

                $type = $item['type'] ?? (in_array($extension, $videoExtensions, true) ? 'video' : 'image');

                if (! in_array($type, ['image', 'video'], true)) {
                    return null;
                }

                return [
                    'id' => "preview-{$index}",
                    'type' => $type,
                    'url' => Storage::disk('public')->url($file),
                    'durationMs' => $this->previewDuration($item),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function previewDuration(array $item): ?int
    {
        $duration = filter_var(
            $item['duration_ms'] ?? null,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 100]],
        );

        return $duration === false ? null : $duration;
    }
}
