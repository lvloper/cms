<?php

declare(strict_types=1);

namespace App\Services\Paco;

use App\Models\Paco\Campaign;
use App\Models\Paco\Intent;
use App\Models\Paco\Playbook;

final class PacoPlaybookResolver
{
    public function resolve(string $intentCode, Campaign $campaign): Playbook
    {
        if ($campaign->preferred_playbook_id && $campaign->preferred_intent_id) {
            return $campaign->playbook()->firstOrFail();
        }

        $intent = Intent::query()->where('code', $intentCode)->first();
        $playbook = $intent?->playbooks()->orderByPivot('priority')->first();

        return $playbook
            ?? $campaign->playbook()->first()
            ?? Playbook::query()->where('code', 'general_discovery')->firstOrFail();
    }
}
