# Plug N Paw Admin Dashboard Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Turn the Laravel Vue starter kit into a seeded, admin-only analytics dashboard for Plug N Paw, deployable free on Render.

**Architecture:** Thin controllers render Inertia pages; per-page `App\Queries\Analytics\*Query` classes compute arrays from SQLite via the query builder; a `DateRange` value object drives every range and previous-period comparison. Deterministic seeders produce the demo data; a `demo:seed` command and a Docker entrypoint rebuild it.

**Tech Stack:** Laravel 13, Fortify, Inertia 3, Vue 3 + TS, Wayfinder, Tailwind 4, reka-ui/shadcn-vue, Chart.js + vue-chartjs, Pest 5, Larastan 7, FrankenPHP, Render.

**Spec:** `docs/superpowers/specs/2026-11-18-plug-n-paw-dashboard-design.md`

## Global Constraints

- SQLite only; never commit `database/*.sqlite` or `.env`.
- Ranges: `7d`, `30d`, `90d`; default `30d`; previous period = same length immediately before.
- Seed window 180 days, Faker seed `2026`, dates relative to `now()` (UTC).
- Admin defaults: `admin@plugnpaw.test` / `password`, overridable by `ADMIN_EMAIL` / `ADMIN_PASSWORD`.
- Sign-up disabled. Reset mail uses the `log` mailer.
- Every analytics route: `auth`, `verified`, `can:view-analytics`.
- Copy: sentence case, plain verbs, no all-caps labels.
- `composer ci:check` must pass (Pint, Larastan level 7, `vp check`, `vue-tsc`, Pest).

## Review Focus

1. A range with zero rows (fresh DB, 7d with no data) → pages render zeros and empty states, deltas show "—" not divide-by-zero. Test in Task 3 (`DateRange`/delta) and Task 4 (empty DB page test).
2. Invalid `range` / filter query params (`?range=1y`, `?status=bogus`) → falls back or 422-redirects without a 500. Test in Task 4.
3. Non-admin verified user → 403 on every analytics route and on lead PATCH/export. Test in Task 2 + Task 5.
4. Demo mode on → password/profile/delete-account requests are refused with a flash, data unchanged. Test in Task 2.
5. Running `demo:seed` twice → identical row counts and identical first-row values (determinism). Test in Task 1.

---

### Task 1: Domain schema, enums, models, factories, deterministic seeders, `demo:seed`

**Files:**
- Create: `app/Enums/{TrafficSource,Device,SearchType,SearchStatus,LeadAction,LeadStatus}.php`
- Create: `app/Models/{Visit,VetSearch,Clinic,ClinicLead,Supporter}.php`
- Create: migrations for `is_admin` on users, `visits`, `clinics`, `vet_searches`, `clinic_leads`, `supporters`
- Create: `database/factories/{Visit,VetSearch,Clinic,ClinicLead,Supporter}Factory.php`
- Create: `database/seeders/{AdminUserSeeder,DemoDataSeeder}.php`, modify `DatabaseSeeder.php`
- Create: `config/demo.php`, `app/Console/Commands/DemoSeedCommand.php`
- Test: `tests/Feature/DemoSeedCommandTest.php`

**Interfaces:**
- Produces: enums are string-backed with `label(): string`; `SearchStatus::isSuccess(): bool` (true for `ok`, `no_results`). Models cast enum columns and `*_at` datetimes. Relations: `Visit hasMany VetSearch`, `VetSearch belongsTo Visit`, `VetSearch hasMany ClinicLead`, `Clinic hasMany ClinicLead`, `ClinicLead belongsTo Clinic|VetSearch`. `User::$is_admin` bool cast. `config('demo.enabled')`, `config('demo.admin.email')`, `config('demo.admin.password')`.

- [ ] Write `DemoSeedCommandTest`: `demo:seed --force` exits 0; admin exists, `is_admin` true, verified; `visits` count > 10 000; `clinic_leads` > 0; every `visits.started_at` within last 180 days; running twice yields identical counts and identical first `vet_searches.query`.
- [ ] Run it, expect FAIL (command missing).
- [ ] Implement schema/enums/models/factories; `DemoDataSeeder` generates arrays with a seeded Faker (`Faker\Factory::create()` + `->seed(2026)`, `mt_srand(2026)`), weekly seasonality + growth, inserts in 500-row chunks inside `DB::transaction`. `demo:seed {--force}` calls `migrate:fresh --seed --force`, confirming when `app()->isProduction()` without `--force`.
- [ ] Update `composer setup` to touch the SQLite file and run `migrate --seed`.
- [ ] Run test → PASS. Commit `feat: domain schema and deterministic demo data`.

### Task 2: Admin-only auth, sign-up off, demo mode

**Files:**
- Modify: `config/fortify.php` (remove registration), `FortifyServiceProvider` (drop register view/action), `AppServiceProvider` (Gate `view-analytics`), `bootstrap/app.php` (trust proxies, `demo` middleware alias), `routes/settings.php`
- Delete: `pages/auth/Register.vue`, `Actions/Fortify/CreateNewUser.php`, `tests/Feature/Auth/RegistrationTest.php`
- Create: `app/Http/Middleware/PreventDemoChanges.php`
- Modify: login view props (`demo` credentials) and `Login.vue`
- Test: `tests/Feature/AdminAccessTest.php`, `tests/Feature/DemoModeTest.php`

**Interfaces:** Produces route middleware alias `demo.readonly`; login page prop `demo: {email, password} | null`.

- [ ] Tests: `/register` 404; non-admin verified user gets 403 on `/dashboard`; guest redirected to login; with `config(['demo.enabled' => true])` PUT `settings/password` leaves hash unchanged and flashes error; DELETE `settings/profile` keeps user; login page has `demo.email` when enabled and `null` when not.
- [ ] Run → FAIL. Implement. Run → PASS (plus existing auth tests). Commit `feat: admin-only access and demo mode`.

### Task 3: `DateRange` + `Period`

**Files:** Create `app/Enums/Period.php`, `app/Support/DateRange.php`, `app/Support/Delta.php`; Test `tests/Unit/DateRangeTest.php`.

**Interfaces:** `Period::from('30d')->days(): int`; `DateRange::forPeriod(Period $p, ?CarbonImmutable $now = null): self`; props `start`, `end` (CarbonImmutable, inclusive day bounds); `previous(): self`; `days(): list<string>` (Y-m-d). `Delta::percent(int|float $current, int|float $previous): ?float` (null when previous is 0).

- [ ] Tests: 7d at 2026-11-18 → start 2026-11-12 00:00, end 2026-11-18 23:59:59; previous → 2026-11-05..2026-11-11; `days()` has 7 entries; `Delta::percent(120,100)` = 20.0; `Delta::percent(5,0)` null.
- [ ] FAIL → implement → PASS. Commit `feat: date range value object`.

### Task 4: Analytics queries + controllers + routes

**Files:**
- Create: `app/Queries/Analytics/{OverviewQuery,TrafficQuery,SearchQuery,LeadQuery,ClinicQuery,SupporterQuery}.php`
- Create: `app/Http/Requests/AnalyticsRequest.php` (`range()` → `DateRange`, invalid → default 30d), `app/Http/Controllers/{Dashboard,Traffic,Search,Lead,Clinic,Supporter}Controller.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/Analytics/*PageTest.php`, `tests/Unit/TrafficQueryTest.php`

**Interfaces:** Each query `__invoke(DateRange $range): array` (or `run(...)`). Page props (all include `range: '7d'|'30d'|'90d'`):
- Overview: `kpis: list<{key,label,value,previous,delta,format}>`, `daily: list<{date,visits,searches,leads}>`, `sources: list<{key,label,value}>`, `topClinics`, `recentLeads`.
- Traffic: `kpis`, `daily`, `sources`, `devices`, `countries: list<{code,value}>`, `landingPages`.
- Searches: `kpis`, `daily: list<{date, ok, no_results, failed}>`, `topQueries`, `searches` (paginator), `filters`.
- Leads: `kpis`, `leads` (paginator with clinic), `filters`, `statuses`, `actions`.
- Clinics: `clinics: list<{id,name,city,country_code,leads,converted,rate}>`.
- Supporters: `kpis`, `monthly: list<{month,amount}>`, `supporters` (paginator).
- KPI `format` ∈ `number|percent|duration|currency|ms`.

- [ ] Tests: each page 200 with expected component and prop keys for admin; `?range=bogus` → 200 with `range` = `30d`; empty DB → 200 and `daily` zero-filled length 30; unit: TrafficQuery counts only rows inside range, zero-fills gaps.
- [ ] FAIL → implement (`DATE(col)` grouping, p95 via ordered offset) → PASS. Commit `feat: analytics queries and pages`.

### Task 5: Lead status update + CSV export

**Files:** Create `app/Http/Requests/UpdateLeadStatusRequest.php`, `app/Policies/ClinicLeadPolicy.php`, export action in `LeadController`; Test `tests/Feature/Analytics/LeadActionsTest.php`.

**Interfaces:** `PATCH /leads/{lead}` (`leads.update`) body `{status}`; `GET /leads/export` (`leads.export`) honours the same filters, streams `text/csv`, filename `clinic-leads-YYYY-MM-DD.csv`.

- [ ] Tests: admin PATCH valid status → 302 + DB updated; invalid status → session error; non-admin → 403; export → 200, `text/csv`, header row `id,occurred_at,clinic,city,action,status`, respects `status` filter.
- [ ] FAIL → implement → PASS. Commit `feat: lead status updates and CSV export`.

### Task 6: Front end — brand, layout, charts, pages

**Files:** Modify `resources/css/app.css`, `vite.config.ts` fonts, `app.blade.php` bg colors, `AppSidebar.vue`, `AppLogo*.vue`; Create `components/analytics/{RangeFilter,KpiCard,ChartCard,LineChart,BarChart,DataPagination,EmptyState,StatusBadge}.vue`, `lib/format.ts`, `lib/chartTheme.ts`; pages `Dashboard.vue`, `analytics/{Traffic,Searches,Leads,Clinics,Supporters}.vue`; delete `Welcome.vue`.

- [ ] Validate categorical palette with the dataviz `validate_palette.js` (light + dark); fix FAILs.
- [ ] Build components and pages; Chart.js reads colors from CSS variables and re-renders on theme change.
- [ ] `npm run types:check` and `npm run check` pass. Commit `feat: dashboard UI`.

### Task 7: Hosting, CI, README

**Files:** Create `Dockerfile`, `.dockerignore`, `docker/entrypoint.sh`, `render.yaml`; modify `.github/workflows/tests.yml` (PHP 8.4), `.env.example` (`APP_NAME`, `DEMO_MODE`, `ADMIN_*`, `MAIL_MAILER=log`); rewrite `README.md`.

- [ ] `docker build` succeeds (if Docker daemon available) and container answers `/up` 200.
- [ ] `composer ci:check` passes. Commit `chore: render deploy, CI and README`.

### Task 8: Verify in browser

- [ ] `php artisan demo:seed --force`, `composer dev`; log in as admin; visit every page in light/dark and at 375px; forgot-password writes link to log; screenshots for README.
