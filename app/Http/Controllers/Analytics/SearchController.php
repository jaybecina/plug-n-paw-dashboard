<?php

namespace App\Http\Controllers\Analytics;

use App\Enums\SearchStatus;
use App\Enums\SearchType;
use App\Http\Controllers\Controller;
use App\Http\Requests\AnalyticsRequest;
use App\Queries\Analytics\SearchQuery;
use Inertia\Inertia;
use Inertia\Response;

class SearchController extends Controller
{
    public function __invoke(AnalyticsRequest $request, SearchQuery $query): Response
    {
        return Inertia::render('analytics/Searches', [
            'range' => $request->period()->value,
            ...$query(
                $request->dateRange(),
                $request->enumFilter('status', SearchStatus::class),
                $request->enumFilter('type', SearchType::class),
            ),
        ]);
    }
}
