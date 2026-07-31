<?php

declare(strict_types=1);

namespace App\Services\Paco;

use App\Models\Paco\Lead;
use Illuminate\Support\Str;

final class PacoCommercialContextEvaluator
{
    public function readyForEvidence(Lead $lead): bool
    {
        return (bool) data_get($lead->state, 'contact_captured', false)
            && (int) data_get($lead->state, 'questions_after_contact', 0) >= 1
            && ! $this->needsProjectContext($lead);
    }

    public function needsProjectContext(Lead $lead): bool
    {
        if (! in_array($lead->primary_intent_code, ['landing_page', 'web_institucional'], true)
            || filled(data_get($lead->state, 'answered_fields.project_context'))) {
            return false;
        }

        $terms = Str::of((string) $lead->problem_summary)
            ->lower()
            ->ascii()
            ->replaceMatches('/[^a-z0-9\s]+/', ' ')
            ->squish()
            ->explode(' ')
            ->reject(fn (string $term): bool => in_array($term, [
                'quiero', 'queremos', 'necesito', 'necesitamos', 'hacer', 'crear', 'desarrollar',
                'una', 'uno', 'un', 'la', 'el', 'para', 'web', 'pagina', 'sitio', 'landing',
            ], true))
            ->filter(fn (string $term): bool => mb_strlen($term) >= 4 || $term === 'ong');

        return $terms->isEmpty();
    }
}
