<?php

declare(strict_types=1);

namespace App\Services\Paco;

use App\Models\Paco\Lead;
use App\Models\Paco\Playbook;

final class PacoSufficiencyEvaluator
{
    /** @return array{sufficient: bool, score: float, missing_high_priority: array<int, string>} */
    public function evaluate(Playbook $playbook, Lead $lead): array
    {
        $state = $lead->state ?? [];
        $answered = array_keys($state['answered_fields'] ?? []);
        $required = $playbook->fields()
            ->where('importance', 'required')
            ->whereNotIn('field_code', ['problem_summary', 'contact'])
            ->pluck('field_code')
            ->unique()
            ->values();
        $highPriority = $playbook->fields()
            ->where('importance', 'high')
            ->whereNotIn('field_code', ['problem_summary', 'contact'])
            ->pluck('field_code')
            ->unique()
            ->values();
        $missingRequired = $required->diff($answered)->values()->all();
        $missing = $highPriority->diff($answered)->values()->all();
        $minimumAfterContact = max(0, (int) ($playbook->settings['minimum_questions_after_contact']
            ?? min(2, $playbook->max_questions_after_contact)));
        $afterContact = (int) ($state['questions_after_contact'] ?? 0);
        $reachedQuestionBudget = $afterContact >= $playbook->max_questions_after_contact;
        $hasProblem = filled($lead->problem_summary);
        $hasContact = (bool) ($state['contact_captured'] ?? false);
        $coverage = $highPriority->isEmpty()
            ? 1.0
            : ($highPriority->count() - count($missing)) / $highPriority->count();
        $afterContactProgress = $minimumAfterContact === 0
            ? 1.0
            : min(1, $afterContact / $minimumAfterContact);
        $score = round(
            ($hasProblem ? 0.25 : 0)
            + ($hasContact ? 0.25 : 0)
            + ($coverage * 0.35)
            + ($afterContactProgress * 0.15),
            3,
        );

        return [
            'sufficient' => $hasProblem
                && $hasContact
                && $missingRequired === []
                && $afterContact >= $minimumAfterContact
                && ($missing === [] || $reachedQuestionBudget)
                && $score >= (float) $playbook->minimum_sufficiency_score,
            'score' => $score,
            'missing_high_priority' => $missing,
        ];
    }
}
