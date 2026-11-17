<?php

namespace App\Queries\Analytics;

use App\Enums\LeadAction;
use App\Enums\LeadStatus;
use App\Models\ClinicLead;
use App\Support\DateRange;
use App\Support\Kpi;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final class LeadQuery
{
    /**
     * @return array<string, mixed>
     */
    public function __invoke(DateRange $range, LeadFilters $filters): array
    {
        $current = $this->totals($range);
        $previous = $this->totals($range->previous());

        return [
            'kpis' => [
                Kpi::make('leads', 'Clinic leads', $current['leads'], $previous['leads']),
                Kpi::make('new', 'Waiting for follow-up', $current['new'], $previous['new'], higherIsBetter: false),
                Kpi::make('converted', 'Converted to a visit', $current['converted'], $previous['converted']),
                Kpi::make('conversion_rate', 'Conversion rate', $current['conversion_rate'], $previous['conversion_rate'], 'percent'),
            ],
            'leads' => $this->paginate($range, $filters),
            'filters' => $filters->toArray(),
            'statuses' => array_map(fn (LeadStatus $s) => ['value' => $s->value, 'label' => $s->label()], LeadStatus::cases()),
            'actions' => array_map(fn (LeadAction $a) => ['value' => $a->value, 'label' => $a->label()], LeadAction::cases()),
        ];
    }

    /**
     * The filtered leads, newest first. Shared by the table and the CSV export.
     *
     * @return Builder<ClinicLead>
     */
    public function filtered(DateRange $range, LeadFilters $filters): Builder
    {
        return ClinicLead::query()
            ->with('clinic:id,name,city,country_code')
            ->whereBetween('occurred_at', $range->bounds())
            ->when($filters->status, fn (Builder $query, LeadStatus $status) => $query->where('status', $status))
            ->when($filters->action, fn (Builder $query, LeadAction $action) => $query->where('action', $action))
            ->when($filters->clinic, fn (Builder $query, string $clinic) => $query->whereHas(
                'clinic',
                // Match % and _ literally: SQLite LIKE needs an explicit ESCAPE.
                fn (Builder $clinics) => $clinics->whereRaw("name like ? escape '\\'", ['%'.addcslashes($clinic, '%_\\').'%']),
            ))
            ->latest('occurred_at')
            ->latest('id');
    }

    /**
     * @return LengthAwarePaginator<int, array{id: int, occurredAt: string, clinic: string, city: string, countryCode: string, action: string, actionLabel: string, status: string}>
     */
    private function paginate(DateRange $range, LeadFilters $filters): LengthAwarePaginator
    {
        return $this->filtered($range, $filters)
            ->paginate(20)
            ->withQueryString()
            ->through(fn (ClinicLead $lead) => [
                'id' => $lead->id,
                'occurredAt' => $lead->occurred_at->toIso8601String(),
                'clinic' => $lead->clinic->name,
                'city' => $lead->clinic->city,
                'countryCode' => $lead->clinic->country_code,
                'action' => $lead->action->value,
                'actionLabel' => $lead->action->label(),
                'status' => $lead->status->value,
            ]);
    }

    /**
     * @return array{leads: int, new: int, converted: int, conversion_rate: float}
     */
    private function totals(DateRange $range): array
    {
        $counts = DB::table('clinic_leads')
            ->whereBetween('occurred_at', $range->bounds())
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $leads = (int) $counts->sum();
        $converted = (int) ($counts[LeadStatus::Converted->value] ?? 0);

        return [
            'leads' => $leads,
            'new' => (int) ($counts[LeadStatus::New->value] ?? 0),
            'converted' => $converted,
            'conversion_rate' => Kpi::share($converted, $leads),
        ];
    }
}
