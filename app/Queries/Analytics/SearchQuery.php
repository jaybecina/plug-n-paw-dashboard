<?php

namespace App\Queries\Analytics;

use App\Enums\SearchStatus;
use App\Enums\SearchType;
use App\Models\VetSearch;
use App\Support\DateRange;
use App\Support\Kpi;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final class SearchQuery
{
    /**
     * @return array<string, mixed>
     */
    public function __invoke(DateRange $range, ?SearchStatus $status = null, ?SearchType $type = null): array
    {
        $current = $this->totals($range);
        $previous = $this->totals($range->previous());

        return [
            'kpis' => [
                Kpi::make('searches', 'Searches', $current['searches'], $previous['searches']),
                Kpi::make('success_rate', 'Answered without error', $current['success_rate'], $previous['success_rate'], 'percent'),
                Kpi::make('p95_latency', 'Slowest 5% take', $current['p95_latency'], $previous['p95_latency'], 'ms', higherIsBetter: false),
                Kpi::make('location_share', 'Used "my location"', $current['location_share'], $previous['location_share'], 'percent'),
            ],
            'daily' => $this->daily($range),
            'topQueries' => $this->searches($range)
                ->whereNotNull('query')
                ->selectRaw('lower(query) as query, count(*) as searches, avg(results_count) as avg_results')
                ->groupByRaw('lower(query)')->orderByDesc('searches')->orderBy('query')->limit(8)->get()
                ->map(fn (object $row) => [
                    'query' => $row->query,
                    'searches' => (int) $row->searches,
                    'avgResults' => round((float) $row->avg_results, 1),
                ])
                ->all(),
            'searches' => $this->log($range, $status, $type),
            'filters' => ['status' => $status?->value, 'type' => $type?->value],
            'statuses' => array_map(fn (SearchStatus $s) => ['value' => $s->value, 'label' => $s->label()], SearchStatus::cases()),
            'types' => array_map(fn (SearchType $t) => ['value' => $t->value, 'label' => $t->label()], SearchType::cases()),
        ];
    }

    /**
     * Daily searches split into answered, answered-empty and failed.
     *
     * @return list<array{date: string, ok: int, no_results: int, failed: int}>
     */
    private function daily(DateRange $range): array
    {
        $rows = $this->searches($range)
            ->selectRaw('date(searched_at) as day, status, count(*) as total')
            ->groupBy('day', 'status')->get()
            ->groupBy('day');

        return array_map(function (string $day) use ($rows) {
            $byStatus = $rows->get($day, collect())->pluck('total', 'status');
            $ok = (int) ($byStatus[SearchStatus::Ok->value] ?? 0);
            $empty = (int) ($byStatus[SearchStatus::NoResults->value] ?? 0);

            return [
                'date' => $day,
                'ok' => $ok,
                'no_results' => $empty,
                'failed' => (int) $byStatus->sum() - $ok - $empty,
            ];
        }, $range->days());
    }

    /**
     * @return LengthAwarePaginator<int, array{id: int, searchedAt: string, type: string, typeLabel: string, query: string|null, countryCode: string, results: int, latencyMs: int, status: string, statusLabel: string, isSuccess: bool}>
     */
    private function log(DateRange $range, ?SearchStatus $status, ?SearchType $type): LengthAwarePaginator
    {
        return VetSearch::query()
            ->whereBetween('searched_at', $range->bounds())
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($type, fn ($query) => $query->where('search_type', $type))
            ->latest('searched_at')->latest('id')
            ->paginate(20)
            ->withQueryString()
            ->through(fn (VetSearch $search) => [
                'id' => $search->id,
                'searchedAt' => $search->searched_at->toIso8601String(),
                'type' => $search->search_type->value,
                'typeLabel' => $search->search_type->label(),
                'query' => $search->query,
                'countryCode' => $search->country_code,
                'results' => $search->results_count,
                'latencyMs' => $search->latency_ms,
                'status' => $search->status->value,
                'statusLabel' => $search->status->label(),
                'isSuccess' => $search->status->isSuccess(),
            ]);
    }

    /**
     * @return array{searches: int, success_rate: float, p95_latency: int, location_share: float}
     */
    private function totals(DateRange $range): array
    {
        $row = $this->searches($range)->selectRaw(
            'count(*) as total, sum(case when status in (?, ?) then 1 else 0 end) as answered, sum(case when search_type = ? then 1 else 0 end) as located',
            [SearchStatus::Ok->value, SearchStatus::NoResults->value, SearchType::Location->value],
        )->first();

        $total = (int) ($row->total ?? 0);

        return [
            'searches' => $total,
            'success_rate' => Kpi::share((int) ($row->answered ?? 0), $total),
            'p95_latency' => $this->p95Latency($range),
            'location_share' => Kpi::share((int) ($row->located ?? 0), $total),
        ];
    }

    /**
     * 95th percentile latency of answered searches (SQLite has no percentile function).
     */
    private function p95Latency(DateRange $range): int
    {
        $answered = $this->searches($range)->whereIn('status', [SearchStatus::Ok->value, SearchStatus::NoResults->value]);
        $count = (clone $answered)->count();

        if ($count === 0) {
            return 0;
        }

        return (int) $answered->orderBy('latency_ms')->offset((int) ceil($count * 0.95) - 1)->value('latency_ms');
    }

    private function searches(DateRange $range): Builder
    {
        return DB::table('vet_searches')->whereBetween('searched_at', $range->bounds());
    }
}
