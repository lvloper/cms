<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Route;
use App\Enums\Status;

class GenerateSitemapCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sitemap:generate {--preview : Preview the sitemap without saving}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate sitemap from published routes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $routes = Route::where('status', Status::Published)
            ->orderBy('full_slug')
            ->get();

        $this->info("Found {$routes->count()} published routes:");
        
        foreach ($routes as $route) {
            $url = $route->full_slug === 'home' ? url('/') : url($route->full_slug);
            $priority = $route->parent_id ? '0.7' : '0.9';
            
            $this->line("  - {$url} (priority: {$priority})");
        }

        if ($this->option('preview')) {
            $this->line("\nSitemap XML preview:");
            $this->line("Access /sitemap.xml to see the full generated sitemap");
        } else {
            $this->info("\nSitemap is dynamically generated at /sitemap.xml");
            $this->info("No static file generation needed - the sitemap reflects real-time route status");
        }

        return 0;
    }
}