<?php

declare(strict_types=1);

namespace App\Data\Paco;

final readonly class AnalysisResult
{
    /**
     * @param  array<int, array{field: string, value: mixed, confidence: float, evidence: string}>  $facts
     * @param  array<int, string>  $questionPriorities
     */
    public function __construct(
        public string $primaryIntent,
        public float $confidence,
        public array $facts = [],
        public array $questionPriorities = [],
        public ?string $acknowledgement = null,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'primary_intent' => $this->primaryIntent,
            'confidence' => $this->confidence,
            'facts' => $this->facts,
            'question_priorities' => $this->questionPriorities,
            'acknowledgement' => $this->acknowledgement,
        ];
    }
}
