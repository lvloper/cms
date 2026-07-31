<?php

declare(strict_types=1);

namespace App\Models\Paco;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ContentImpression extends Model
{
    protected $fillable = [
        'conversation_id', 'event_id', 'entity_type', 'entity_id',
        'presentation_type', 'rank', 'reason_code',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }
}
