<?php

declare(strict_types=1);

namespace App\Models\Paco;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Question extends Model
{
    protected $fillable = [
        'code', 'field_code', 'prompt', 'short_prompt', 'component_type', 'options',
        'is_sensitive', 'is_skippable', 'validation_schema', 'status', 'version',
    ];

    protected $casts = [
        'options' => 'array',
        'validation_schema' => 'array',
        'is_sensitive' => 'boolean',
        'is_skippable' => 'boolean',
    ];

    public function playbookFields(): HasMany
    {
        return $this->hasMany(PlaybookField::class);
    }
}
