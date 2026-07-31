<?php

declare(strict_types=1);

namespace App\Models\Paco;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Campaign extends Model
{
    protected $fillable = [
        'code', 'name', 'status', 'initial_message', 'context', 'preferred_playbook_id',
        'preferred_intent_id', 'max_interactions', 'allowed_origins', 'starts_at', 'ends_at',
    ];

    protected $casts = [
        'context' => 'array',
        'allowed_origins' => 'array',
        'starts_at' => 'immutable_datetime',
        'ends_at' => 'immutable_datetime',
    ];

    public function scopeAvailable(Builder $query): Builder
    {
        return $query
            ->where('status', 'active')
            ->where(fn (Builder $builder): Builder => $builder->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn (Builder $builder): Builder => $builder->whereNull('ends_at')->orWhere('ends_at', '>=', now()));
    }

    public function playbook(): BelongsTo
    {
        return $this->belongsTo(Playbook::class, 'preferred_playbook_id');
    }

    public function intent(): BelongsTo
    {
        return $this->belongsTo(Intent::class, 'preferred_intent_id');
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }
}
