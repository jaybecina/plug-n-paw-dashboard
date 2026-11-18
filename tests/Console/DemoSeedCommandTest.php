<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

function demoSnapshot(): array
{
    return [
        'visits' => DB::table('visits')->count(),
        'vet_searches' => DB::table('vet_searches')->count(),
        'clinic_leads' => DB::table('clinic_leads')->count(),
        'clinics' => DB::table('clinics')->count(),
        'supporters' => DB::table('supporters')->count(),
        'first_query' => DB::table('vet_searches')->whereNotNull('query')->orderBy('id')->value('query'),
        'first_clinic' => DB::table('clinics')->orderBy('id')->value('name'),
    ];
}

it('rebuilds the demo database with an admin account', function () {
    $this->artisan('demo:seed', ['--force' => true])->assertSuccessful();

    $admin = User::where('email', config('demo.admin.email'))->first();

    expect($admin)->not->toBeNull()
        ->and($admin->is_admin)->toBeTrue()
        ->and($admin->email_verified_at)->not->toBeNull();
});

it('seeds realistic volumes inside the last 180 days', function () {
    $this->artisan('demo:seed', ['--force' => true])->assertSuccessful();

    $snapshot = demoSnapshot();

    expect($snapshot['visits'])->toBeGreaterThan(1_500)->toBeLessThan(3_500)
        ->and($snapshot['vet_searches'])->toBeGreaterThan(800)->toBeLessThan($snapshot['visits'])
        ->and($snapshot['clinic_leads'])->toBeGreaterThan(0)->toBeLessThan($snapshot['vet_searches'])
        ->and($snapshot['supporters'])->toBeGreaterThan(0)
        ->and(DB::table('visits')->where('started_at', '<', now()->subDays(180)->startOfDay())->count())->toBe(0)
        ->and(DB::table('visits')->where('started_at', '>', now())->count())->toBe(0);
});

it('keeps a typical 30-day window at small-site volumes', function () {
    $this->artisan('demo:seed', ['--force' => true])->assertSuccessful();

    $since = now()->subDays(30);

    expect(DB::table('visits')->where('started_at', '>=', $since)->count())->toBeLessThan(500)
        ->and(DB::table('vet_searches')->where('searched_at', '>=', $since)->count())->toBeLessThan(400)
        ->and(DB::table('clinic_leads')->where('occurred_at', '>=', $since)->count())->toBeLessThan(100);
});

it('produces identical data on every run', function () {
    $this->artisan('demo:seed', ['--force' => true])->assertSuccessful();
    $first = demoSnapshot();

    $this->artisan('demo:seed', ['--force' => true])->assertSuccessful();

    expect(demoSnapshot())->toBe($first);
});
