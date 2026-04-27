<?php

namespace App\Models\Traits;

trait HasPreview
{
    public function isPreview(): bool
    {
        return request()->query('preview') === $this->getPreviewHash();
    }

    public function getPreviewHash(): string
    {
        return substr(hash('md5', $this->routable->id . config('app.key')), 0, 8);
    }

    public function getPreviewUrlAttribute(): string
    {
        return url($this->getFullPath()) . '?preview=' . $this->getPreviewHash();
    }
} 