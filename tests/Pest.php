<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| Closure-based Pest tests in Feature/ run on the app TestCase with a fresh
| in-memory SQLite database per test. Unit/ tests stay framework-free unless
| they opt in.
|
*/

pest()->extend(TestCase::class)->use(RefreshDatabase::class)->in('Feature');
pest()->extend(TestCase::class)->in('Unit');

// Console tests run commands that rebuild the schema themselves (migrate:fresh),
// which can't happen inside RefreshDatabase's wrapping transaction.
pest()->extend(TestCase::class)->in('Console');
