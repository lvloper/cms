<?php

declare(strict_types=1);

namespace App\Contracts\Paco;

use App\Data\Paco\AnalysisResult;
use App\Data\Paco\EvidencePlan;
use App\Data\Paco\TurnInterpretation;

interface PacoModelGateway
{
    /** @param array<string, mixed> $context */
    public function analyze(string $message, ?string $campaignIntent = null, array $context = []): AnalysisResult;

    /** @param array<string, mixed> $context */
    public function interpretTurn(string $message, array $context): TurnInterpretation;

    /**
     * @param  array<string, mixed>  $context
     * @param  array<int, array<string, mixed>>  $candidates
     */
    public function planEvidence(array $context, array $candidates): EvidencePlan;

    public function provider(): string;

    public function model(): string;

    public function usedFallback(): bool;
}
