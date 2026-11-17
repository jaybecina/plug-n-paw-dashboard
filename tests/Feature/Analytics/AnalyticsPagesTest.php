<?php

use App\Enums\LeadStatus;
use App\Models\ClinicLead;
use App\Models\Supporter;
use App\Models\User;
use App\Models\VetSearch;
use App\Models\Visit;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->actingAs(User::factory()->admin()->create());
});

dataset('pages', [
    'overview' => ['dashboard', 'Dashboard', ['kpis', 'daily', 'sources', 'topClinics', 'recentLeads']],
    'traffic' => ['traffic', 'analytics/Traffic', ['kpis', 'daily', 'sources', 'devices', 'countries', 'landingPages']],
    'searches' => ['searches', 'analytics/Searches', ['kpis', 'daily', 'topQueries', 'searches', 'filters']],
    'leads' => ['leads.index', 'analytics/Leads', ['kpis', 'leads', 'filters', 'statuses', 'actions']],
    'clinics' => ['clinics', 'analytics/Clinics', ['clinics']],
    'supporters' => ['supporters', 'analytics/Supporters', ['kpis', 'monthly', 'supporters']],
]);

it('renders each analytics page with its data', function (string $route, string $component, array $props) {
    Visit::factory()->count(5)->create();
    VetSearch::factory()->count(5)->create();
    ClinicLead::factory()->count(3)->create();
    Supporter::factory()->count(2)->create();

    $this->get(route($route))->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component($component)
        ->where('range', '30d')
        ->hasAll($props)
    );
})->with('pages');

it('falls back to 30 days for an unknown range', function (string $route, string $component) {
    $this->get(route($route, ['range' => '1y']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component($component)->where('range', '30d'));
})->with('pages');

it('renders an empty database with zero-filled days', function () {
    $this->get(route('dashboard', ['range' => '7d']))->assertOk()->assertInertia(fn (Assert $page) => $page
        ->where('range', '7d')
        ->has('daily', 7)
        ->where('daily.0.visits', 0)
        ->where('kpis.0.value', 0)
        ->where('kpis.0.delta', null)
    );
});

it('ignores unknown filter values instead of failing', function () {
    $this->get(route('searches', ['status' => 'bogus', 'type' => 'nope']))->assertOk();
    $this->get(route('leads.index', ['status' => 'bogus', 'action' => 'nope']))->assertOk();
});

it('filters leads by status', function () {
    ClinicLead::factory()->count(2)->status(LeadStatus::Converted)->create();
    ClinicLead::factory()->count(3)->status(LeadStatus::New)->create();

    $this->get(route('leads.index', ['status' => 'converted']))->assertInertia(fn (Assert $page) => $page
        ->has('leads.data', 2)
        ->where('leads.data.0.status', 'converted')
        ->where('filters.status', 'converted')
    );
});

it('forbids non-admins on every analytics page', function (string $route) {
    $this->actingAs(User::factory()->create())->get(route($route))->assertForbidden();
})->with(['dashboard', 'traffic', 'searches', 'leads.index', 'clinics', 'supporters']);
