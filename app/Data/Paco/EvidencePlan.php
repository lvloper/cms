<?php

declare(strict_types=1);

namespace App\Data\Paco;

final readonly class EvidencePlan
{
    /**
     * @param  array<int, string>  $selectedItemIds
     */
    public function __construct(
        public array $selectedItemIds,
        public string $relationship,
        public ?string $acknowledgement = null,
        public ?string $testimonialItemId = null,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'selected_item_ids' => $this->selectedItemIds,
            'relationship' => $this->relationship,
            'acknowledgement' => $this->acknowledgement,
            'testimonial_item_id' => $this->testimonialItemId,
        ];
    }
}
