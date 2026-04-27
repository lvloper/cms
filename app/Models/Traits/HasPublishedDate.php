<?php

namespace App\Models\Traits;

use Carbon\Carbon;

trait HasPublishedDate
{
    public static bool $hasPublishedDate = true;

    public function initializeHasPublishedDate()
    {
        $this->casts['published_at'] = 'datetime';
    }

    public function formattedPublishedDate( $time = false ): string
    {
        if (!$this->isDatePublished()) {
            return 'No publicado aún';
        }
        $format = $time ? 'dddd, D [de] MMMM [de] YYYY [a las] H:mm' : 'dddd, D [de] MMMM [de] YYYY';
        $formattedDate = ucfirst($this->published_at
            ->locale('es-ES')
            ->isoFormat($format));
            
        return preg_replace_callback('/\b(de )([\p{L}]+)/u', function($matches) {
            return $matches[1] . ucfirst($matches[2]);
        }, $formattedDate);
    }

    public function publishedTimeAgo(): string
    {
        if (!$this->isDatePublished()) {
            return 'No publicado aún';
        }

        return $this->published_at->diffForHumans();
    }

    public function isDatePublished(): bool
    {
        return $this->published_at && $this->published_at <= now();
    }

    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    public function scopeUnpublished($query)
    {
        return $query->whereNull('published_at')->orWhere('published_at', '>', now());
    }
}