@php
    $editUrl = null;
    $user = auth()->user();
    $isAdmin = $user && method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['super_admin', 'admin']);

    if ($isAdmin && isset($route) && $route instanceof \App\Models\Route) {
        try {
            $routable = $route->relationLoaded('routable') ? $route->routable : $route->routable()->first();

            if ($routable instanceof \Illuminate\Database\Eloquent\Model) {
                foreach (\Filament\Facades\Filament::getResources() as $resourceClass) {
                    if (! is_subclass_of($resourceClass, \Filament\Resources\Resource::class)) {
                        continue;
                    }

                    if ($resourceClass::getModel() !== $routable::class || ! $resourceClass::hasPage('edit')) {
                        continue;
                    }

                    if (! $resourceClass::canEdit($routable)) {
                        continue;
                    }

                    $editUrl = $resourceClass::getUrl('edit', ['record' => $routable], panel: 'admin');

                    break;
                }
            }
        } catch (\Throwable) {
            $editUrl = null;
        }
    }
@endphp

@if($editUrl)
    <a
        href="{{ $editUrl }}"
        target="_blank"
        rel="noopener noreferrer"
        class="fixed bottom-4 left-3 z-[80] inline-flex h-8 w-8 items-center justify-center rounded-full bg-gray-500 text-white transition-colors duration-150 hover:bg-gray-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-gray-600 focus-visible:ring-offset-2 sm:bottom-5 sm:right-4"
        aria-label="Editar esta pagina"
        title="Editar esta pagina"
    >
        <x-lucide-pencil class="h-4 w-4" stroke-width="2.5" />
    </a>
@endif
