<?php

namespace App\Queries\Analytics;

use App\Enums\LeadAction;
use App\Enums\LeadStatus;

final readonly class LeadFilters
{
    public function __construct(
        public ?LeadStatus $status = null,
        public ?LeadAction $action = null,
        public ?string $clinic = null,
    ) {}

    /**
     * @return array{status: string|null, action: string|null, clinic: string|null}
     */
    public function toArray(): array
    {
        return [
            'status' => $this->status?->value,
            'action' => $this->action?->value,
            'clinic' => $this->clinic,
        ];
    }
}
