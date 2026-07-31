<?php

namespace App\Http\Controllers;

use App\Enums\Status;
use App\Models\Client;
use App\Models\Route;
use App\Services\BlockDataPreprocessor;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ClientController extends Controller
{
    public function show(Request $request, Route $route, Client $client)
    {
        $clientData = [
            'id' => $client->id,
            'paco_campaign' => config('paco.direct_campaign'),
            'navigation' => $this->clientNavigation($client),
            'title' => $route->title,
            'logo' => $this->storageUrl($client->logo),
            'hero_eyebrow' => $client->hero_eyebrow,
            'hero_title' => $client->hero_title,
            'hero_summary' => $client->hero_summary,
            'relationship_since' => $client->relationship_since,
            'hero_services' => $client->hero_services?->values()->all() ?? [],
            'hero_media_type' => $client->hero_media_type,
            'hero_media_image' => $this->storageUrl($client->hero_media_image),
            'hero_media_video' => $this->storageUrl($client->hero_media_video),
            'hero_media_alt' => $client->hero_media_alt,
            'hero_media_placeholder' => $client->hero_media_placeholder,
            'hero_media_autoplay' => $client->hero_media_autoplay,
            'works' => $this->withResolvedImages($client->works, resolveCategories: true),
            'testimonials' => $this->withResolvedImages($client->testimonials),
        ];

        if (config('cms.frontend') === 'react') {
            $blocks = (new BlockDataPreprocessor)->process(
                $client->blocks?->toArray() ?? []
            );

            return Inertia::render('Cms/Client', [
                'client' => $clientData,
                'blocks' => $blocks,
                'route' => [
                    'id' => $route->id,
                    'title' => $route->title,
                    'slug' => $route->slug,
                    'full_slug' => $route->full_slug,
                    'layout' => $route->layout ?? 'default',
                    'description' => $route->description,
                    'custom_css' => $route->custom_css,
                    'header_scripts' => $route->header_scripts,
                    'footer_scripts' => $route->footer_scripts,
                ],
                'layout' => $route->layout ?? 'default',
            ]);
        }

        return view('clients.show', [
            'client' => $client,
            'clientData' => $clientData,
            'route' => $route,
        ]);
    }

    /**
     * @return array{previous: array<string, mixed>, next: array<string, mixed>}|null
     */
    private function clientNavigation(Client $current): ?array
    {
        $clients = Client::query()
            ->whereHas('route', fn ($query) => $query->where('status', Status::Published))
            ->with('route')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $currentIndex = $clients->search(fn (Client $client): bool => $client->is($current));
        $count = $clients->count();

        if ($currentIndex === false || $count < 2) {
            return null;
        }

        $previous = $clients->get(($currentIndex - 1 + $count) % $count);
        $next = $clients->get(($currentIndex + 1) % $count);

        return [
            'previous' => $this->clientNavigationItem($previous),
            'next' => $this->clientNavigationItem($next),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function clientNavigationItem(Client $client): array
    {
        $title = $client->route?->title ?: ($client->public_name ?: 'Cliente');

        return [
            'id' => $client->id,
            'src' => $this->storageUrl($client->logo),
            'alt' => $title,
            'title' => $title,
            'url' => url($client->route?->getFullPath() ?? '#'),
            'color' => $client->color,
            'popupTextColor' => $client->popup_text_color,
            'testimonial' => $this->testimonialPreview($client->testimonials?->first()),
            'previewItems' => $this->previewItems($client),
        ];
    }

    /**
     * @param  array<string, mixed>|null  $testimonial
     * @return array{text: string, person: string, position: string}|null
     */
    private function testimonialPreview(?array $testimonial): ?array
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
        $testimonial = $this->testimonialPreview($client->testimonials?->first());

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

    private function storageUrl(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        return str_starts_with($path, 'http') ? $path : Storage::url($path);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function withResolvedImages(?Collection $items, bool $resolveCategories = false): array
    {
        return ($items ?? collect())
            ->map(function (mixed $item) use ($resolveCategories): array {
                $item = is_array($item) ? $item : [];
                $item['image'] = $this->storageUrl($item['image'] ?? null);

                if ($resolveCategories) {
                    $item['categories'] = collect($item['categories'] ?? [])
                        ->map(fn (string $category): string => Client::WORK_CATEGORIES[$category] ?? $category)
                        ->values()
                        ->all();
                }

                return $item;
            })
            ->values()
            ->all();
    }
}
