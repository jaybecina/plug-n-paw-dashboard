<?php

namespace App\Http\Controllers\Analytics;

use App\Enums\LeadAction;
use App\Enums\LeadStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\AnalyticsRequest;
use App\Http\Requests\UpdateLeadStatusRequest;
use App\Models\ClinicLead;
use App\Queries\Analytics\LeadFilters;
use App\Queries\Analytics\LeadQuery;
use App\Support\CsvSafe;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LeadController extends Controller
{
    public function index(AnalyticsRequest $request, LeadQuery $query): Response
    {
        return Inertia::render('analytics/Leads', [
            'range' => $request->period()->value,
            ...$query($request->dateRange(), $this->filters($request)),
        ]);
    }

    public function update(UpdateLeadStatusRequest $request, ClinicLead $lead): RedirectResponse
    {
        $lead->update(['status' => $request->status()]);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Lead marked as :status.', ['status' => strtolower($lead->status->label())]),
        ]);

        return back();
    }

    /**
     * Stream the leads matching the current filters as CSV.
     */
    public function export(AnalyticsRequest $request, LeadQuery $query): StreamedResponse
    {
        $leads = $query->filtered($request->dateRange(), $this->filters($request));

        return response()->streamDownload(function () use ($leads) {
            $out = fopen('php://output', 'w');

            if ($out === false) {
                return;
            }

            fputcsv($out, ['id', 'occurred_at', 'clinic', 'city', 'country', 'action', 'status'], escape: '');

            foreach ($leads->lazy(500) as $lead) {
                fputcsv($out, array_map(CsvSafe::cell(...), [
                    $lead->id,
                    $lead->occurred_at->toDateTimeString(),
                    $lead->clinic->name,
                    $lead->clinic->city,
                    $lead->clinic->country_code,
                    $lead->action->value,
                    $lead->status->value,
                ]), escape: '');
            }

            fclose($out);
        }, 'clinic-leads-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function filters(AnalyticsRequest $request): LeadFilters
    {
        return new LeadFilters(
            status: $request->enumFilter('status', LeadStatus::class),
            action: $request->enumFilter('action', LeadAction::class),
            clinic: $request->string('clinic')->trim()->toString() ?: null,
        );
    }
}
