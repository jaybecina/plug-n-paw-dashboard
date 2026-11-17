<?php

use App\Enums\Period;
use App\Enums\TrafficSource;
use App\Models\Visit;
use App\Queries\Analytics\TrafficQuery;
use App\Support\DateRange;
use Carbon\CarbonImmutable;

beforeEach(function () {
    $this->now = CarbonImmutable::parse('2026-11-18 12:00:00');
    $this->range = DateRange::forPeriod(Period::Week, $this->now);
});

it('counts only visits inside the range and compares with the previous one', function () {
    Visit::factory()->count(4)->bounced(false)->create(['started_at' => '2026-11-15 09:00:00']);
    Visit::factory()->count(1)->bounced()->create(['started_at' => '2026-11-18 08:00:00']);
    Visit::factory()->count(2)->create(['started_at' => '2026-11-08 10:00:00']); // previous week
    Visit::factory()->count(9)->create(['started_at' => '2026-10-01 10:00:00']); // ignored

    $kpis = collect((new TrafficQuery)($this->range)['kpis'])->keyBy('key');

    expect($kpis['visits']['value'])->toBe(5)
        ->and($kpis['visits']['previous'])->toBe(2)
        ->and($kpis['visits']['delta'])->toBe(150.0)
        ->and($kpis['bounce_rate']['value'])->toBe(20.0);
});

it('zero-fills days without visits', function () {
    Visit::factory()->create(['started_at' => '2026-11-14 10:00:00']);

    $daily = (new TrafficQuery)($this->range)['daily'];

    expect($daily)->toHaveCount(7)
        ->and($daily[0])->toBe(['date' => '2026-11-12', 'visits' => 0])
        ->and($daily[2])->toBe(['date' => '2026-11-14', 'visits' => 1]);
});

it('groups visits by source with readable labels, biggest first', function () {
    Visit::factory()->count(3)->create(['started_at' => '2026-11-16 10:00:00', 'source' => TrafficSource::Organic]);
    Visit::factory()->count(1)->create(['started_at' => '2026-11-16 10:00:00', 'source' => TrafficSource::Email]);

    $sources = (new TrafficQuery)($this->range)['sources'];

    expect($sources[0])->toBe(['key' => 'organic', 'label' => 'Organic search', 'value' => 3])
        ->and($sources[1])->toBe(['key' => 'email', 'label' => 'Email', 'value' => 1]);
});
