<?php

namespace App\Http\Controllers\Analytics;

use App\Http\Controllers\Controller;
use App\Http\Requests\AnalyticsRequest;
use App\Queries\Analytics\SupporterQuery;
use Inertia\Inertia;
use Inertia\Response;

class SupporterController extends Controller
{
    public function __invoke(AnalyticsRequest $request, SupporterQuery $query): Response
    {
        return Inertia::render('analytics/Supporters', [
            'range' => $request->period()->value,
            ...$query($request->dateRange()),
        ]);
    }
}
