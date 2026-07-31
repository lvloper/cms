<?php

declare(strict_types=1);

namespace App\Models\Paco;

use App\Enums\Paco\FitStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ServiceFitRule extends Model
{
    protected $fillable = [
        'code', 'intent_id', 'status', 'conditions', 'approved_response_block_id',
        'alternative_service_ids', 'priority', 'version', 'active',
    ];

    protected $casts = [
        'status' => FitStatus::class,
        'conditions' => 'array',
        'alternative_service_ids' => 'array',
        'active' => 'boolean',
    ];

    public function intent(): BelongsTo
    {
        return $this->belongsTo(Intent::class);
    }

    public function responseBlock(): BelongsTo
    {
        return $this->belongsTo(ResponseBlock::class, 'approved_response_block_id');
    }
}
