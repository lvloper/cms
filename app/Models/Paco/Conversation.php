<?php

declare(strict_types=1);

namespace App\Models\Paco;

use App\Enums\Paco\ConversationStage;
use App\Enums\Paco\ConversationStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

final class Conversation extends Model
{
    use HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'public_token_hash', 'campaign_id', 'playbook_id', 'status', 'stage', 'locale',
        'origin_url', 'origin_host', 'referrer', 'utm_source', 'utm_medium', 'utm_campaign',
        'client_ip_hash', 'country_code', 'user_agent_summary', 'interaction_count',
        'useful_interaction_count', 'version', 'last_activity_at', 'closed_at',
    ];

    protected $hidden = ['public_token_hash', 'client_ip_hash'];

    protected $casts = [
        'status' => ConversationStatus::class,
        'stage' => ConversationStage::class,
        'last_activity_at' => 'immutable_datetime',
        'closed_at' => 'immutable_datetime',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function playbook(): BelongsTo
    {
        return $this->belongsTo(Playbook::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(ConversationEvent::class)->orderBy('sequence');
    }

    public function lead(): HasOne
    {
        return $this->hasOne(Lead::class);
    }

    public function modelRuns(): HasMany
    {
        return $this->hasMany(ModelRun::class);
    }
}
