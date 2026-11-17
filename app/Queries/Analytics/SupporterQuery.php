<?php

namespace App\Queries\Analytics;

use App\Models\Supporter;
use App\Support\DateRange;
use App\Support\Kpi;
use Carbon\CarbonImmutable;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final class SupporterQuery
{
    public const MONTHS = 6;

    /**
     * @return array<string, mixed>
     */
    public function __invoke(DateRange $range): array
    {
        $current = $this->totals($range);
        $previous = $this->totals($range->previous());

        return [
            'kpis' => [
                Kpi::make('raised', 'Raised', $current['raised'], $previous['raised'], 'currency'),
                Kpi::make('supporters', 'Supporters', $current['supporters'], $previous['supporters']),
                Kpi::make('average', 'Average gift', $current['average'], $previous['average'], 'currency'),
                Kpi::make('monthly_share', 'Gave monthly', $current['monthly_share'], $previous['monthly_share'], 'percent'),
            ],
            'monthly' => $this->monthly($range->end),
            'supporters' => $this->paginate($range),
        ];
    }

    /**
     * Amount raised per calendar month for the last six months, regardless of range.
     *
     * @return list<array{month: string, amount: int}>
     */
    private function monthly(CarbonImmutable $end): array
    {
        $start = $end->startOfMonth()->subMonths(self::MONTHS - 1);

        $totals = DB::table('supporters')
            ->whereBetween('donated_at', [$start->toDateTimeString(), $end->toDateTimeString()])
            ->selectRaw("strftime('%Y-%m', donated_at) as month, sum(amount_cents) as amount")
            ->groupBy('month')
            ->pluck('amount', 'month');

        return array_map(function (int $offset) use ($start, $totals) {
            $month = $start->addMonths($offset)->format('Y-m');

            return ['month' => $month, 'amount' => (int) ($totals[$month] ?? 0)];
        }, range(0, self::MONTHS - 1));
    }

    /**
     * @return LengthAwarePaginator<int, array{id: int, name: string|null, amountCents: int, currency: string, message: string|null, isMonthly: bool, donatedAt: string}>
     */
    private function paginate(DateRange $range): LengthAwarePaginator
    {
        return Supporter::query()
            ->whereBetween('donated_at', $range->bounds())
            ->latest('donated_at')->latest('id')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Supporter $supporter) => [
                'id' => $supporter->id,
                'name' => $supporter->name,
                'amountCents' => $supporter->amount_cents,
                'currency' => $supporter->currency,
                'message' => $supporter->message,
                'isMonthly' => $supporter->is_monthly,
                'donatedAt' => $supporter->donated_at->toIso8601String(),
            ]);
    }

    /**
     * @return array{raised: int, supporters: int, average: int, monthly_share: float}
     */
    private function totals(DateRange $range): array
    {
        $row = DB::table('supporters')
            ->whereBetween('donated_at', $range->bounds())
            ->selectRaw('count(*) as supporters, coalesce(sum(amount_cents), 0) as raised, sum(is_monthly) as monthly')
            ->first();

        $supporters = (int) ($row->supporters ?? 0);
        $raised = (int) ($row->raised ?? 0);

        return [
            'raised' => $raised,
            'supporters' => $supporters,
            'average' => $supporters > 0 ? intdiv($raised, $supporters) : 0,
            'monthly_share' => Kpi::share((int) ($row->monthly ?? 0), $supporters),
        ];
    }
}
