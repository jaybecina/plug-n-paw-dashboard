<?php

namespace Database\Factories;

use App\Enums\Device;
use App\Enums\TrafficSource;
use App\Models\Visit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Visit>
 */
class VisitFactory extends Factory
{
    public function definition(): array
    {
        $bounced = fake()->boolean(35);

        return [
            'session_uuid' => fake()->uuid(),
            'landing_page' => fake()->randomElement(['/', '/find-vet']),
            'source' => fake()->randomElement(TrafficSource::cases()),
            'referrer_host' => null,
            'device' => fake()->randomElement(Device::cases()),
            'country_code' => fake()->randomElement(['PH', 'US', 'GB', 'AU']),
            'page_views' => $bounced ? 1 : fake()->numberBetween(2, 6),
            'duration_seconds' => $bounced ? fake()->numberBetween(3, 40) : fake()->numberBetween(45, 600),
            'bounced' => $bounced,
            'started_at' => fake()->dateTimeBetween('-29 days'),
        ];
    }

    public function bounced(bool $bounced = true): static
    {
        return $this->state(fn () => [
            'bounced' => $bounced,
            'page_views' => $bounced ? 1 : 3,
        ]);
    }
}
