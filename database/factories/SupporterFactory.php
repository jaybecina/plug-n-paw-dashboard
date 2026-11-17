<?php

namespace Database\Factories;

use App\Models\Supporter;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Supporter>
 */
class SupporterFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->optional()->firstName(),
            'amount_cents' => fake()->randomElement([300, 500, 1000, 2500]),
            'currency' => 'USD',
            'message' => fake()->optional()->sentence(),
            'is_monthly' => fake()->boolean(20),
            'donated_at' => fake()->dateTimeBetween('-29 days'),
        ];
    }
}
