<?php

namespace App\Providers;

use App\Contracts\Paco\PacoModelGateway;
use App\Services\ConfigurationService;
use App\Services\Paco\DeterministicPacoModelGateway;
use App\Services\Paco\OpenCodeGoModelGateway;
use Filament\Support\Facades\FilamentView;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
        FilamentView::registerRenderHook('panels::body.end', fn (): string => Blade::render("@vite('resources/js/hot-reload.js')"));

        // Register Configuration Service
        $this->app->singleton('app.config', function ($app) {
            return new ConfigurationService;
        });

        $this->app->bind(PacoModelGateway::class, function ($app): PacoModelGateway {
            return config('paco.model_driver') === 'opencode_go'
                ? $app->make(OpenCodeGoModelGateway::class)
                : $app->make(DeterministicPacoModelGateway::class);
        });

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register Blade directive
        Blade::directive('block', function ($key) {
            $key = trim($key, "'");

            return Blade::compileString('<x-block key=\'.$key.\'></x-block>');
        });

        Gate::before(function ($user, $ability) {
            if (method_exists($user, 'hasRole') && $user->hasRole('super_admin')) {
                return true;
            }
        });
    }
}
