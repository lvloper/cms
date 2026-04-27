<?php

namespace App\Services;

use App\Models\Configuration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class ConfigurationService
{
    /**
     * Cache time in minutes
     */
    protected $cacheTime = 60;

    /**
     * Get a configuration value by key
     * 
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        return Cache::remember("config.{$key}", $this->cacheTime, function () use ($key, $default) {
            $config = Configuration::where('key', $key)->first();
            return $config ? $config->value : $default;
        });
    }

    /**
     * Get a text configuration value
     * 
     * @param string $key
     * @param string $default
     * @return string
     */
    public function text(string $key, string $default = ''): string
    {
        $config = $this->get($key);
        return is_array($config) ? ($config['text'] ?? $default) : $default;
    }

    /**
     * Get a rich text configuration value
     * 
     * @param string $key
     * @param string $default
     * @return string
     */
    public function richText(string $key, string $default = ''): string
    {
        $config = $this->get($key);
        return is_array($config) ? ($config['rich_content'] ?? $default) : $default;
    }

    /**
     * Get a URL configuration value
     * 
     * @param string $key
     * @param array $default
     * @return array
     */
    public function url(string $key, array $default = []): array
    {
        $config = $this->get($key);
        return is_array($config) ? ($config['route'] ?? $default) : $default;
    }

    /**
     * Get an image configuration value
     * 
     * @param string $key
     * @param string $default
     * @return string
     */
    public function image(string $key, string $default = ''): string
    {
        $config = $this->get($key);
        return is_array($config) ? ($config['image'] ?? $default) : $default;
    }

    /**
     * Get the URL string from a URL configuration
     * 
     * @param string $key
     * @param string $default
     * @return string
     */
    public function urlString(string $key, string $default = ''): string
    {
        $urlConfig = $this->url($key);
        
        if (empty($urlConfig)) {
            return $default;
        }

        // Si es una URL externa
        if (isset($urlConfig['external_url']) && !empty($urlConfig['external_url'])) {
            return $urlConfig['external_url'];
        }

        // Si es un archivo
        if (isset($urlConfig['file']) && !empty($urlConfig['file'])) {
            return Storage::url($urlConfig['file']);
        }

        // Si es una ruta interna
        if (isset($urlConfig['route_id']) && $urlConfig['route_id'] > 0) {
            $route = \App\Models\Route::find($urlConfig['route_id']);
            if ($route) {
                $url = url($route->full_slug);
                if (isset($urlConfig['anchor']) && !empty($urlConfig['anchor'])) {
                    $url .= '#' . $urlConfig['anchor'];
                }
                return $url;
            }
        }

        return $default;
    }

    /**
     * Check if a URL configuration should open in new window
     * 
     * @param string $key
     * @return bool
     */
    public function urlNewWindow(string $key): bool
    {
        $urlConfig = $this->url($key);
        return $urlConfig['new_window'] ?? false;
    }

    /**
     * Get the target attribute for a URL configuration
     * 
     * @param string $key
     * @return string
     */
    public function urlTarget(string $key): string
    {
        return $this->urlNewWindow($key) ? '_blank' : '_self';
    }

    /**
     * Clear configuration cache
     * 
     * @param string|null $key
     * @return void
     */
    public function clearCache(?string $key = null): void
    {
        if ($key) {
            Cache::forget("config.{$key}");
        } else {
            // Clear all config cache
            $configurations = Configuration::all();
            foreach ($configurations as $config) {
                Cache::forget("config.{$config->key}");
            }
        }
    }

    /**
     * Set a configuration value
     * 
     * @param string $key
     * @param mixed $value
     * @param string $type
     * @return Configuration
     */
    public function set(string $key, $value, string $type = 'text'): Configuration
    {
        $this->clearCache($key);
        return Configuration::setValue($key, $value, $type);
    }
}
