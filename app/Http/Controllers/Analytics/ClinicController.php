<?php

namespace App\Http\Controllers\Analytics;

use App\Http\Controllers\Controller;
use App\Http\Requests\AnalyticsRequest;
use App\Queries\Analytics\ClinicQuery;
use Inertia\Inertia;
use Inertia\Response;

class ClinicController extends Controller
{
    public function __invoke(AnalyticsRequest $request, ClinicQuery $query): Response
    {
        return Inertia::render('analytics/Clinics', [
            'range' => $request->period()->value,
            ...$query($request->dateRange()),
        ]);
    }
}
