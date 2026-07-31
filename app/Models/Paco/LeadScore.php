<?php

declare(strict_types=1);

namespace App\Models\Paco;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class LeadScore extends Model
{
    protected $fillable = [
        'lead_id', 'score_total', 'fit_score', 'clarity_score', 'scale_score', 'decision_score',
        'readiness_score', 'timing_score', 'interaction_score', 'rules_version', 'explanation',
    ];

    protected $casts = ['explanation' => 'array'];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }
}
