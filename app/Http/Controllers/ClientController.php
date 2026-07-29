<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Route;
use App\Services\BlockDataPreprocessor;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ClientController extends Controller
{
    public function show(Request $request, Route $route, Client $client)
    {
        $clientData = [
            'id' => $client->id,
            'title' => $route->title,
            'logo' => $this->storageUrl($client->logo),
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
