<?php

namespace App\Queries\Analytics;

use App\Enums\LeadAction;
use App\Enums\LeadStatus;
use App\Enums\SearchStatus;
use App\Enums\TrafficSource;
use App\Support\DateRange;
use App\Support\Kpi;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class OverviewQuery
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
                Kpi::make('searches', 'Vet searches', $current['searches'], $previous['searches']),
                Kpi::make('leads', 'Clinic leads', $current['leads'], $previous['leads']),
                Kpi::make('lead_rate', 'Searches that became leads', $current['lead_rate'], $previous['lead_rate'], 'percent'),
            ],
            'daily' => $this->daily($range),
            'sources' => DB::table('visits')
                ->whereBetween('started_at', $range->bounds())
                ->selectRaw('source as `key`, count(*) as value')
                ->groupBy('key')->orderByDesc('value')->get()
                ->map(fn (object $row) => ['key' => $row->key, 'label' => TrafficSource::from($row->key)->label(), 'value' => (int) $row->value])
                ->all(),
            'topClinics' => DB::table('clinic_leads')
                ->join('clinics', 'clinics.id', '=', 'clinic_leads.clinic_id')
                ->whereBetween('clinic_leads.occurred_at', $range->bounds())
                ->selectRaw('clinics.id, clinics.name, clinics.city, clinics.country_code, count(*) as leads')
                ->groupBy('clinics.id', 'clinics.name', 'clinics.city', 'clinics.country_code')
                ->orderByDesc('leads')->limit(5)->get()
                ->map(fn (object $row) => [
                    'id' => (int) $row->id,
                    'name' => $row->name,
                    'city' => $row->city,
                    'countryCode' => $row->country_code,
                    'leads' => (int) $row->leads,
                ])
                ->all(),
            'recentLeads' => DB::table('clinic_leads')
                ->join('clinics', 'clinics.id', '=', 'clinic_leads.clinic_id')
                ->whereBetween('clinic_leads.occurred_at', $range->bounds())
                ->orderByDesc('clinic_leads.occurred_at')->orderByDesc('clinic_leads.id')->limit(6)
                ->get(['clinic_leads.id', 'clinic_leads.action', 'clinic_leads.status', 'clinic_leads.occurred_at', 'clinics.name', 'clinics.city'])
                ->map(fn (object $row) => [
                    'id' => (int) $row->id,
                    'clinic' => $row->name,
                    'city' => $row->city,
                    'action' => LeadAction::from($row->action)->label(),
                    'status' => $row->status,
                    'statusLabel' => LeadStatus::from($row->status)->label(),
                    'occurredAt' => CarbonImmutable::parse($row->occurred_at)->toIso8601String(),
                ])
                ->all(),
        ];
    }

    /**
     * @return list<array<string, int|string>>
     */
    private function daily(DateRange $range): array
    {
        $visits = $this->perDay('visits', $range);
        $searches = $this->perDay('vet_searches', $range);
        $leads = $this->perDay('clinic_leads', $range);

        return array_map(fn (string $day) => [
            'date' => $day,
            'visits' => (int) ($visits[$day] ?? 0),
            'searches' => (int) ($searches[$day] ?? 0),
            'leads' => (int) ($leads[$day] ?? 0),
        ], $range->days());
    }

    /**
     * Row counts per day for one activity table.
     *
     * @param  'visits'|'vet_searches'|'clinic_leads'  $table
     * @return Collection<string, int>
     */
    private function perDay(string $table, DateRange $range): Collection
    {
        [$column, $day] = match ($table) {
            'visits' => ['started_at', 'date(started_at) as day'],
            'vet_searches' => ['searched_at', 'date(searched_at) as day'],
            'clinic_leads' => ['occurred_at', 'date(occurred_at) as day'],
        };

        return DB::table($table)
            ->whereBetween($column, $range->bounds())
            ->selectRaw($day)
            ->selectRaw('count(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day')
            ->map(fn (mixed $total) => (int) $total);
    }

    /**
     * @return array{visits: int, searches: int, leads: int, lead_rate: float}
     */
    private function totals(DateRange $range): array
    {
        $searches = DB::table('vet_searches')->whereBetween('searched_at', $range->bounds());
        $successful = (clone $searches)->where('status', SearchStatus::Ok->value)->count();
        $leads = DB::table('clinic_leads')->whereBetween('occurred_at', $range->bounds())->count();

        return [
            'visits' => DB::table('visits')->whereBetween('started_at', $range->bounds())->count(),
            'searches' => $searches->count(),
            'leads' => $leads,
            'lead_rate' => Kpi::share($leads, $successful),
        ];
    }
}
