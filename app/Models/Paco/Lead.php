<?php

declare(strict_types=1);

namespace App\Models\Paco;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Lead extends Model
{
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'conversation_id', 'status', 'name', 'organization_name', 'role_title', 'contact_channel',
        'email', 'phone_e164', 'primary_intent_code', 'consultation_type', 'fit_level', 'score',
        'score_confidence', 'next_action', 'summary', 'problem_summary', 'country_code',
        'employees_range', 'revenue_range', 'decision_role', 'urgency', 'deadline',
        'budget_mentioned_amount', 'budget_mentioned_currency', 'state', 'qualified_at',
    ];

    protected $casts = [
        'email' => 'encrypted',
        'phone_e164' => 'encrypted',
        'state' => 'array',
        'deadline' => 'date',
        'qualified_at' => 'immutable_datetime',
        'score_confidence' => 'decimal:3',
        'budget_mentioned_amount' => 'decimal:2',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function attributes(): HasMany
    {
        return $this->hasMany(LeadAttribute::class);
    }

    public function scores(): HasMany
    {
        return $this->hasMany(LeadScore::class);
    }
}
