<?php

namespace App\Queries\Analytics;

use App\Enums\Device;
use App\Enums\TrafficSource;
use App\Support\DateRange;
use App\Support\Kpi;
use App\Support\Series;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

final class TrafficQuery
{
    /**
     * @return array<string, mixed>
     */
    public function __invoke(DateRange $range): array
    {
        $current = $this->totals($range);
        $previous = $this->totals($range->previous());

        return [
            'kpis' => [
                Kpi::make('visits', 'Visits', $current['visits'], $previous['visits']),
                Kpi::make('bounce_rate', 'Bounce rate', $current['bounce_rate'], $previous['bounce_rate'], 'percent', higherIsBetter: false),
                Kpi::make('avg_duration', 'Avg. time on site', $current['avg_duration'], $previous['avg_duration'], 'duration'),
                Kpi::make('pages_per_visit', 'Pages per visit', $current['pages_per_visit'], $previous['pages_per_visit']),
            ],
            'daily' => Series::daily(
                $range,
                $this->visits($range)->selectRaw('date(started_at) as day, count(*) as visits')->groupBy('day')->get(),
                ['visits'],
            ),
            'sources' => $this->breakdown($range, 'source', fn (string $key) => TrafficSource::from($key)->label()),
            'devices' => $this->breakdown($range, 'device', fn (string $key) => Device::from($key)->label()),
            'countries' => $this->visits($range)
                ->selectRaw('country_code as code, count(*) as value')
                ->groupBy('code')->orderByDesc('value')->limit(8)->get()
                ->map(fn (object $row) => ['code' => $row->code, 'value' => (int) $row->value])
                ->all(),
            'landingPages' => $this->visits($range)
                ->selectRaw('landing_page as path, count(*) as visits, avg(bounced) * 100 as bounce_rate')
                ->groupBy('path')->orderByDesc('visits')->get()
                ->map(fn (object $row) => [
                    'path' => $row->path,
                    'visits' => (int) $row->visits,
                    'bounceRate' => round((float) $row->bounce_rate, 1),
                ])
                ->all(),
        ];
    }

    /**
     * Visit counts by a column, biggest first, with display labels.
     *
     * @param  'source'|'device'  $column
     * @param  callable(string): string  $label
     * @return list<array{key: string, label: string, value: int}>
     */
    private function breakdown(DateRange $range, string $column, callable $label): array
    {
        return array_values($this->visits($range)
            ->select("{$column} as key")
            ->selectRaw('count(*) as value')
            ->groupBy('key')->orderByDesc('value')->orderBy('key')->get()
            ->map(fn (object $row) => ['key' => (string) $row->key, 'label' => $label((string) $row->key), 'value' => (int) $row->value])
            ->all());
    }

    /**
     * @return array{visits: int, bounce_rate: float, avg_duration: int, pages_per_visit: float}
     */
    private function totals(DateRange $range): array
    {
        $row = $this->visits($range)
            ->selectRaw('count(*) as visits, sum(bounced) as bounced, avg(duration_seconds) as duration, avg(page_views) as pages')
            ->first();

        $visits = (int) ($row->visits ?? 0);

        return [
            'visits' => $visits,
            'bounce_rate' => Kpi::share((int) ($row->bounced ?? 0), $visits),
            'avg_duration' => (int) round((float) ($row->duration ?? 0)),
            'pages_per_visit' => round((float) ($row->pages ?? 0), 1),
        ];
    }

    private function visits(DateRange $range): Builder
    {
        return DB::table('visits')->whereBetween('started_at', $range->bounds());
    }
}
