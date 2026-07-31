<?php

declare(strict_types=1);

namespace App\Models\Paco;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ResponseBlock extends Model
{
    protected $fillable = [
        'code', 'block_type', 'intent_id', 'stage', 'text', 'allowed_variables',
        'adaptation_mode', 'status', 'priority', 'version',
    ];

    protected $casts = ['allowed_variables' => 'array'];

    public function intent(): BelongsTo
    {
        return $this->belongsTo(Intent::class);
    }
}
