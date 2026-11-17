<?php

namespace App\Http\Controllers\Analytics;

use App\Enums\LeadAction;
use App\Enums\LeadStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\AnalyticsRequest;
use App\Queries\Analytics\LeadFilters;
use App\Queries\Analytics\LeadQuery;
use Inertia\Inertia;
use Inertia\Response;

class LeadController extends Controller
{
    public function index(AnalyticsRequest $request, LeadQuery $query): Response
    {
        return Inertia::render('analytics/Leads', [
            'range' => $request->period()->value,
            ...$query($request->dateRange(), $this->filters($request)),
        ]);
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
