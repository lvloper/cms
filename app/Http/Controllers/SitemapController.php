<?php

namespace App\Http\Controllers;

use App\Models\Route;
use App\Enums\Status;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index()
    {
        // Get all published routes using the scope
        $routes = Route::forSitemap()
            ->orderBy('full_slug')
            ->get();

        $xml = $this->generateSitemapXml($routes);

        return response($xml, 200)
            ->header('Content-Type', 'application/xml');
    }

    private function generateSitemapXml($routes)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($routes as $route) {
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . htmlspecialchars($route->getCanonicalUrl()) . '</loc>' . "\n";
            $xml .= '    <lastmod>' . $route->updated_at->toAtomString() . '</lastmod>' . "\n";
            $xml .= '    <changefreq>weekly</changefreq>' . "\n";
            $xml .= '    <priority>' . $route->getSitemapPriority() . '</priority>' . "\n";
            $xml .= '  </url>' . "\n";
        }

        $xml .= '</urlset>';

        return $xml;
    }
}