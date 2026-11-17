<?php

namespace App\Queries\Analytics;

use App\Enums\LeadStatus;
use App\Support\DateRange;
use App\Support\Kpi;
use Illuminate\Support\Facades\DB;

final class ClinicQuery
{
    /**
     * Every clinic ranked by the leads Plug N Paw sent it in the range.
     *
     * @return array{clinics: list<array{id: int, name: string, city: string, countryCode: string, leads: int, calls: int, converted: int, conversionRate: float}>}
     */
    public function __invoke(DateRange $range): array
    {
        [$from, $to] = $range->bounds();

        $clinics = array_values(DB::table('clinics')
            ->leftJoin('clinic_leads', function ($join) use ($from, $to) {
                $join->on('clinic_leads.clinic_id', '=', 'clinics.id')
                    ->whereBetween('clinic_leads.occurred_at', [$from, $to]);
            })
            ->selectRaw(
                'clinics.id, clinics.name, clinics.city, clinics.country_code, count(clinic_leads.id) as leads, sum(case when clinic_leads.status = ? then 1 else 0 end) as converted, sum(case when clinic_leads.action = ? then 1 else 0 end) as calls',
                [LeadStatus::Converted->value, 'call'],
            )
            ->groupBy('clinics.id', 'clinics.name', 'clinics.city', 'clinics.country_code')
            ->orderByDesc('leads')->orderBy('clinics.name')
            ->get()
            ->map(fn (object $row) => [
                'id' => (int) $row->id,
                'name' => (string) $row->name,
                'city' => (string) $row->city,
                'countryCode' => (string) $row->country_code,
                'leads' => (int) $row->leads,
                'calls' => (int) $row->calls,
                'converted' => (int) $row->converted,
                'conversionRate' => Kpi::share((int) $row->converted, (int) $row->leads),
            ])
            ->all());

        return ['clinics' => $clinics];
    }
}
