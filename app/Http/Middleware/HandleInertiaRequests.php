<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    public function handle($request, $next)
    {
        if (config('cms.frontend') !== 'react') {
            return $next($request);
        }

        return parent::handle($request, $next);
    }

    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'shared' => [
                'menu' => $this->resolveMenu(),
            ],
        ];
    }

    protected function resolveMenu(): array
    {
        if (config('cms.frontend') !== 'react') {
            return [];
        }

        $routes = \App\Models\Route::query()
            ->whereNotNull('slug')
            ->whereNull('parent_id')
            ->orderBy('title')
            ->limit(20)
            ->get();

        return $routes->map(fn ($r) => [
            'title' => $r->title,
            'url' => url($r->full_slug),
        ])->toArray();
    }
}
