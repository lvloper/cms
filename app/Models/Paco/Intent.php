<?php

declare(strict_types=1);

namespace App\Models\Paco;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Intent extends Model
{
    protected $fillable = ['code', 'name', 'description', 'type', 'status'];

    public function playbooks(): BelongsToMany
    {
        return $this->belongsToMany(Playbook::class)->withPivot('priority');
    }

    public function fitRules(): HasMany
    {
        return $this->hasMany(ServiceFitRule::class);
    }
}
