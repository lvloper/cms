<?php

declare(strict_types=1);

namespace App\Models\Paco;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Playbook extends Model
{
    protected $fillable = [
        'code', 'name', 'objective', 'status', 'max_interactions',
        'max_questions_after_contact', 'minimum_sufficiency_score', 'settings', 'version',
    ];

    protected $casts = [
        'settings' => 'array',
        'minimum_sufficiency_score' => 'decimal:3',
    ];

    public function intents(): BelongsToMany
    {
        return $this->belongsToMany(Intent::class)->withPivot('priority');
    }

    public function fields(): HasMany
    {
        return $this->hasMany(PlaybookField::class)->orderBy('priority');
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class, 'preferred_playbook_id');
    }
}
