<?php

declare(strict_types=1);

namespace App\Data\Paco;

final readonly class ActionResult
{
    /** @param array<string, mixed>|null $turn */
    public function __construct(
        public bool $useful,
        public bool $accepted,
        public ?array $turn = null,
    ) {}

    public static function accepted(bool $useful = true): self
    {
        return new self(useful: $useful, accepted: true);
    }

    /** @param array<string, mixed> $turn */
    public static function reroute(array $turn): self
    {
        return new self(useful: false, accepted: false, turn: $turn);
    }
}
