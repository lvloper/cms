<?php

declare(strict_types=1);

namespace App\Models\Paco;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class SecurityEvent extends Model
{
    protected $fillable = ['conversation_id', 'ip_hash', 'event_type', 'severity', 'metadata'];

    protected $casts = ['metadata' => 'array'];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }
}
