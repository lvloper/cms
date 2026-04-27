<?php

namespace App\Models\Traits;

use App\Models\Route;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use App\Enums\Status;

trait HasRoute
{
    protected static function booted()
    {
        parent::boot();

        static::deleting(function ($model) {
            $model->route()->delete();
        });
    }

    public function route(): MorphOne
    {
        return $this->morphOne(Route::class, 'routable');
    }

    public function isPublished(): bool
    {
        return $this->route?->is_published ?? false;
    }


    public function getFullPath(): string
    {
        return $this->route?->getFullPath() ?? '';
    }

    public function getUrlAttribute(): string
    {
        return url($this->getFullPath());
    }

    public function getTitleAttribute(): string
    {
        return $this->route?->title ?? '';
    }

    public function getDescriptionAttribute(): string
    {
        return $this->route?->description ?? '';
    }

    public function getStatusAttribute(): string
    {
        if ($this->route) {
            return $this->route->status->name;
        }
        
        return '';
    }


    public function getPreviewUrlAttribute(): string
    {
        return $this->route?->previewUrl ?? '';
    }


    public function parent()
    {
        return $this->route->parent;
    }

    public function scopeIsPublished($query)
    {
        $modelClass = get_class($query->getModel());
        $checkDate = property_exists($modelClass, 'editDate') && $modelClass::$editDate;
    
        return $query->whereHas('route', function ($q) use ($checkDate) {
            $q->where('status', Status::Published);
            if ($checkDate) {
                $q->where('published_at', '<=', now());
            }
        });
    }
}
