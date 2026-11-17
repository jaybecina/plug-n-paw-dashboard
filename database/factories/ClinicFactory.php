<?php

namespace Database\Factories;

use App\Models\Clinic;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Clinic>
 */
class ClinicFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->lastName().' Veterinary Clinic',
            'city' => fake()->city(),
            'country_code' => 'PH',
            'lat' => fake()->latitude(),
            'lon' => fake()->longitude(),
            'phone' => fake()->optional()->phoneNumber(),
        ];
    }
}
