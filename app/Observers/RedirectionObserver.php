<?php

namespace App\Observers;

use App\Models\Redirection;
use Illuminate\Support\Facades\Cache;

class RedirectionObserver
{
    /**
     * Handle the Redirection "created" event.
     */
    public function created(Redirection $redirection): void
    {
        $this->clearRedirectionCache($redirection);
    }

    /**
     * Handle the Redirection "updated" event.
     */
    public function updated(Redirection $redirection): void
    {
        $this->clearRedirectionCache($redirection);
        
        // También limpiar el cache de la URL anterior si cambió
        if ($redirection->isDirty('old_url')) {
            $oldUrl = $redirection->getOriginal('old_url');
            $this->clearCacheForUrl($oldUrl);
        }
    }

    /**
     * Handle the Redirection "deleted" event.
     */
    public function deleted(Redirection $redirection): void
    {
        $this->clearRedirectionCache($redirection);
    }

    /**
     * Handle the Redirection "restored" event.
     */
    public function restored(Redirection $redirection): void
    {
        $this->clearRedirectionCache($redirection);
    }

    /**
     * Clear cache for a specific redirection
     */
    private function clearRedirectionCache(Redirection $redirection): void
    {
    $this->clearCacheForUrl($redirection->old_url);
    }

    /**
     * Clear cache for a specific URL
     */
    private function clearCacheForUrl(string $url): void
    {
    $normalized = Redirection::normalizePath($url);
    Cache::forget('redirection:' . md5($normalized));
    // Limpiar variantes comunes por compatibilidad
    Cache::forget('redirection:' . md5(ltrim($normalized, '/')));
    Cache::forget('redirection:' . md5($normalized . '/'));
    }
}