<?php

namespace Database\Factories;

use App\Enums\SearchStatus;
use App\Enums\SearchType;
use App\Models\VetSearch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VetSearch>
 */
class VetSearchFactory extends Factory
{
    public function definition(): array
    {
        return [
            'visit_id' => null,
            'search_type' => SearchType::Place,
            'query' => fake()->city(),
            'country_code' => 'PH',
            'results_count' => fake()->numberBetween(3, 20),
            'latency_ms' => fake()->numberBetween(150, 900),
            'status' => SearchStatus::Ok,
            'searched_at' => fake()->dateTimeBetween('-29 days'),
        ];
    }

    public function location(): static
    {
        return $this->state(fn () => ['search_type' => SearchType::Location, 'query' => null]);
    }

    public function failed(SearchStatus $status = SearchStatus::UpstreamTimeout): static
    {
        return $this->state(fn () => ['status' => $status, 'results_count' => 0]);
    }
}
