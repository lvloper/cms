<?php

declare(strict_types=1);

namespace App\Models\Paco;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class LeadAttribute extends Model
{
    protected $fillable = [
        'lead_id', 'field_code', 'value_json', 'value_text', 'evidence_type', 'evidence_text',
        'confidence', 'source_event_id', 'is_current', 'surface_to_user', 'superseded_at',
    ];

    protected $casts = [
        'value_json' => 'array',
        'confidence' => 'decimal:3',
        'is_current' => 'boolean',
        'surface_to_user' => 'boolean',
        'superseded_at' => 'immutable_datetime',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function sourceEvent(): BelongsTo
    {
        return $this->belongsTo(ConversationEvent::class, 'source_event_id');
    }
}
