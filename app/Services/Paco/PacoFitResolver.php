<?php

declare(strict_types=1);

namespace App\Services\Paco;

use App\Enums\Paco\FitStatus;
use App\Models\Paco\Intent;
use App\Models\Paco\ServiceFitRule;

final class PacoFitResolver
{
    public function resolve(string $intentCode): FitStatus
    {
        $intent = Intent::query()->where('code', $intentCode)->first();

        if (! $intent) {
            return FitStatus::Unknown;
        }

        $status = ServiceFitRule::query()
            ->where('intent_id', $intent->id)
            ->where('active', true)
            ->orderBy('priority')
            ->value('status');

        return $status instanceof FitStatus
            ? $status
            : FitStatus::tryFrom((string) $status) ?? FitStatus::Unknown;
    }
}
