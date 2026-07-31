<?php

declare(strict_types=1);

namespace App\Data\Paco;

final readonly class TurnInterpretation
{
    public function __construct(
        public string $disposition,
        public bool $answersCurrentQuestion,
        public bool $useful,
        public float $confidence,
        public ?string $normalizedAnswer = null,
        public ?string $reply = null,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'disposition' => $this->disposition,
            'answers_current_question' => $this->answersCurrentQuestion,
            'useful' => $this->useful,
            'confidence' => $this->confidence,
            'normalized_answer' => $this->normalizedAnswer,
            'reply' => $this->reply,
        ];
    }
}
