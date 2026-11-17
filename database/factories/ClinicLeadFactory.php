<?php

namespace Database\Factories;

use App\Enums\LeadAction;
use App\Enums\LeadStatus;
use App\Models\Clinic;
use App\Models\ClinicLead;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClinicLead>
 */
class ClinicLeadFactory extends Factory
{
    public function definition(): array
    {
        return [
            'clinic_id' => Clinic::factory(),
            'vet_search_id' => null,
            'action' => fake()->randomElement(LeadAction::cases()),
            'status' => LeadStatus::New,
            'occurred_at' => fake()->dateTimeBetween('-29 days'),
        ];
    }

    public function status(LeadStatus $status): static
    {
        return $this->state(fn () => ['status' => $status]);
    }
}
