<?php

declare(strict_types=1);

namespace App\Services\Paco;

use App\Models\Paco\Lead;
use App\Models\Paco\Playbook;
use App\Models\Paco\Question;
use Illuminate\Support\Str;

final class PacoQuestionSelector
{
    public function __construct(private readonly PacoCommercialContextEvaluator $commercialContext) {}

    public function next(Playbook $playbook, Lead $lead): ?Question
    {
        $state = $lead->state ?? [];
        $answered = array_keys($state['answered_fields'] ?? []);
        $asked = $state['asked_questions'] ?? [];
        $hasContact = (bool) ($state['contact_captured'] ?? false);
        $questionsAfterContact = (int) ($state['questions_after_contact'] ?? 0);
        if ($hasContact && $questionsAfterContact >= $playbook->max_questions_after_contact) {
            return null;
        }

        $fields = $playbook->fields()
            ->with('question')
            ->whereNotIn('field_code', ['problem_summary', 'contact'])
            ->whereNotIn('field_code', $answered)
            ->whereHas('question', function ($query) use ($asked, $hasContact): void {
                $query->whereNotIn('code', $asked)->where('status', 'active');
                if (! $hasContact) {
                    $query->where('is_sensitive', false);
                }
            })
            ->orderByRaw("case importance when 'required' then 1 when 'high' then 2 when 'medium' then 3 else 4 end")
            ->orderBy('priority')
            ->get();

        $required = $fields->first(fn ($field): bool => $field->importance === 'required');
        if ($required?->question) {
            return $required->question;
        }

        if ($hasContact && $this->commercialContext->needsProjectContext($lead)) {
            $projectContext = $fields->firstWhere('field_code', 'project_context');
            if ($projectContext?->question) {
                return $projectContext->question;
            }
        }

        foreach ($state['question_priority_codes'] ?? [] as $questionCode) {
            $planned = Question::query()
                ->where('code', $questionCode)
                ->where('status', 'active')
                ->whereNotIn('code', $asked)
                ->whereNotIn('field_code', $answered)
                ->when(! $hasContact, fn ($query) => $query->where('is_sensitive', false))
                ->first();

            if ($planned && $this->contextuallyEligible($planned, $lead)) {
                return $planned;
            }
        }

        $importanceRank = ['high' => 1, 'medium' => 2, 'low' => 3];
        $topRank = $fields->map(fn ($field): int => $importanceRank[$field->importance] ?? 5)->min();
        $topFields = $fields->filter(
            fn ($field): bool => ($importanceRank[$field->importance] ?? 5) === $topRank,
        );

        return $topFields->first()?->question;
    }

    private function contextuallyEligible(Question $question, Lead $lead): bool
    {
        if ($question->field_code === 'budget_context') {
            return (bool) data_get($lead->state, 'contact_captured', false)
                && (int) data_get($lead->state, 'questions_after_contact', 0) >= 1;
        }

        if ($question->field_code !== 'organization_name') {
            return true;
        }

        $problem = Str::of((string) $lead->problem_summary)->lower()->ascii()->toString();

        return collect([
            'empresa', 'organizacion', 'fundacion', 'ong', 'equipo', 'institucion', 'asociacion', 'cooperativa',
        ])->contains(fn (string $term): bool => str_contains($problem, $term))
            || data_get($lead->state, 'answered_fields.organization_structure') === 'organization';
    }
}
