<?php

namespace App\Http\Controllers\Analytics;

use App\Http\Controllers\Controller;
use App\Http\Requests\AnalyticsRequest;
use App\Queries\Analytics\OverviewQuery;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(AnalyticsRequest $request, OverviewQuery $query): Response
    {
        return Inertia::render('Dashboard', [
            'range' => $request->period()->value,
            ...$query($request->dateRange()),
        ]);
    }
}
