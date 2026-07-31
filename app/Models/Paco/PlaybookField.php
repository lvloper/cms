<?php

declare(strict_types=1);

namespace App\Models\Paco;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PlaybookField extends Model
{
    protected $fillable = [
        'playbook_id', 'field_code', 'importance', 'ask_condition', 'question_id', 'priority',
    ];

    protected $casts = ['ask_condition' => 'array'];

    public function playbook(): BelongsTo
    {
        return $this->belongsTo(Playbook::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
