<?php

use App\Enums\Period;
use App\Support\DateRange;
use App\Support\Delta;
use Carbon\CarbonImmutable;

$now = CarbonImmutable::parse('2026-11-18 15:30:00');

it('covers whole days ending today', function () use ($now) {
    $range = DateRange::forPeriod(Period::Week, $now);

    expect($range->start->toDateTimeString())->toBe('2026-11-12 00:00:00')
        ->and($range->end->toDateTimeString())->toBe('2026-11-18 23:59:59')
        ->and($range->days())->toHaveCount(7)
        ->and($range->days()[0])->toBe('2026-11-12')
        ->and($range->days()[6])->toBe('2026-11-18');
});

it('compares against the same length right before it', function () use ($now) {
    $previous = DateRange::forPeriod(Period::Week, $now)->previous();

    expect($previous->start->toDateTimeString())->toBe('2026-11-05 00:00:00')
        ->and($previous->end->toDateTimeString())->toBe('2026-11-11 23:59:59');
});

it('maps every period to its length in days', function () {
    expect(Period::from('7d')->days())->toBe(7)
        ->and(Period::from('30d')->days())->toBe(30)
        ->and(Period::from('90d')->days())->toBe(90)
        ->and(Period::default())->toBe(Period::Month);
});

it('calculates percentage change', function () {
    expect(Delta::percent(120, 100))->toBe(20.0)
        ->and(Delta::percent(75, 100))->toBe(-25.0)
        ->and(Delta::percent(0, 0))->toBeNull()
        ->and(Delta::percent(5, 0))->toBeNull();
});
