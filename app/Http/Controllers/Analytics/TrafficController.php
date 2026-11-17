<?php

namespace App\Http\Controllers\Analytics;

use App\Http\Controllers\Controller;
use App\Http\Requests\AnalyticsRequest;
use App\Queries\Analytics\TrafficQuery;
use Inertia\Inertia;
use Inertia\Response;

class TrafficController extends Controller
{
    public function __invoke(AnalyticsRequest $request, TrafficQuery $query): Response
    {
        return Inertia::render('analytics/Traffic', [
            'range' => $request->period()->value,
            ...$query($request->dateRange()),
        ]);
    }
}
