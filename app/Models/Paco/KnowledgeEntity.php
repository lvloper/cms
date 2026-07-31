<?php

declare(strict_types=1);

namespace App\Models\Paco;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

final class KnowledgeEntity extends Model
{
    protected $fillable = [
        'entity_type', 'entity_id', 'title', 'summary', 'chat_text', 'url', 'image_url',
        'chat_enabled', 'published', 'locale', 'metadata',
    ];

    protected $casts = [
        'chat_enabled' => 'boolean',
        'published' => 'boolean',
        'metadata' => 'array',
    ];

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('published', true)->where('chat_enabled', true);
    }
}
