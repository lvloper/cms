<?php

declare(strict_types=1);

namespace App\Models\Paco;

use Illuminate\Database\Eloquent\Model;

final class KnowledgeChunk extends Model
{
    protected $fillable = [
        'source_type', 'source_id', 'field_path', 'locale', 'plain_text', 'content_hash',
        'embedding', 'embedding_model', 'embedding_dimensions', 'embedding_version',
        'metadata', 'published_at', 'indexed_at',
    ];

    protected $casts = [
        'embedding' => 'array',
        'metadata' => 'array',
        'published_at' => 'immutable_datetime',
        'indexed_at' => 'immutable_datetime',
    ];
}
