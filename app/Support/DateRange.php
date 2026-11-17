<?php

namespace App\Support;

use App\Enums\Period;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;

/**
 * An inclusive range of whole days, used to filter and compare analytics.
 */
final readonly class DateRange
{
    public function __construct(
        public CarbonImmutable $start,
        public CarbonImmutable $end,
        public Period $period,
    ) {}

    public static function forPeriod(Period $period, ?CarbonImmutable $now = null): self
    {
        $end = ($now ?? CarbonImmutable::now())->endOfDay();

        return new self($end->subDays($period->days() - 1)->startOfDay(), $end, $period);
    }

    /**
     * The equal-length range immediately before this one.
     */
    public function previous(): self
    {
        $end = $this->start->subDay()->endOfDay();

        return new self($end->subDays($this->period->days() - 1)->startOfDay(), $end, $this->period);
    }

    /**
     * Every day in the range as Y-m-d, oldest first.
     *
     * @return list<string>
     */
    public function days(): array
    {
        return array_map(
            fn ($day) => $day->format('Y-m-d'),
            iterator_to_array(CarbonPeriod::create($this->start->startOfDay(), '1 day', $this->end->startOfDay()), false),
        );
    }

    /**
     * Bounds formatted for SQLite string comparison.
     *
     * @return array{string, string}
     */
    public function bounds(): array
    {
        return [$this->start->toDateTimeString(), $this->end->toDateTimeString()];
    }
}
