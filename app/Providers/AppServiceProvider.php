<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Filament\Support\Facades\FilamentView;
use FilamentTiptapEditor\TiptapEditor;

use App\TiptapBlocks\LineTitle;
use App\TiptapBlocks\Button;
use App\TiptapBlocks\Video;
use App\TiptapBlocks\Code;
use App\TiptapBlocks\Gallery;
use Filament\Support\Facades\FilamentAsset;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
        FilamentView::registerRenderHook('panels::body.end', fn(): string => Blade::render("@vite('resources/js/hot-reload.js')"));

        // Register Configuration Service
        $this->app->singleton('app.config', function ($app) {
            return new \App\Services\ConfigurationService();
        });

        TiptapEditor::configureUsing(function (TiptapEditor $component) {
            $component
                ->collapseBlocksPanel(true)

                ->blocks([
                    // LineTitle::class,
                    Button::class,
                    Video::class,
                    Code::class,
                    Gallery::class,
                ]);
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

        // Register third-party plugin policies
        Gate::policy(\Saade\FilamentLaravelLog\Pages\ViewLog::class, \App\Policies\LogPolicy::class);

        Gate::before(function ($user, $ability) {
            if (method_exists($user, 'hasRole') && $user->hasRole('super_admin')) {
                return true;
            }
        });
    }
}
