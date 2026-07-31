<?php

declare(strict_types=1);

namespace App\Models\Paco;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ModelRun extends Model
{
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'conversation_id', 'phase', 'provider', 'model', 'prompt_version', 'input_hash',
        'input_snapshot', 'output_snapshot', 'validated', 'validation_errors', 'latency_ms',
        'input_tokens', 'output_tokens', 'cost_estimate', 'request_id',
    ];

    protected $casts = [
        'input_snapshot' => 'array',
        'output_snapshot' => 'array',
        'validated' => 'boolean',
        'validation_errors' => 'array',
        'cost_estimate' => 'decimal:6',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }
}
