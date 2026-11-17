# Plug N Paw Admin Dashboard — Design Spec

Date: 2026-11-18
Status: Approved ("go, proceed Render")

## 1. Intent

A portfolio piece: an admin dashboard for the live Plug N Paw vet-finder
site (https://plug-n-paw-web.vercel.app). Clients open the hosted demo and
log in; developers clone the GitHub repo and get the exact same data with
one command. It must read as senior Laravel work: idiomatic structure,
tests, static analysis, CI, honest README.

## 2. Goals / non-goals / anti-goals

Goals

- Laravel 13 + Inertia 3 + Vue 3 (TypeScript) on the official Vue starter kit.
- SQLite only.
- Login, forgot password, seeded admin account; sign-up disabled.
- `php artisan demo:seed` rebuilds a deterministic demo database.
- Tables + charts about what the real site does: traffic, vet searches,
  clinic leads ("Open in Maps" / call clicks), Ko-fi supporters.
- Free hosting on Render (Docker), demo resets on every boot.
- README a stranger can follow.

Non-goals

- No real ingestion from the live site (data is seeded, labelled as demo).
- No AI-triage leads (that feature is not live on the site).
- No multi-tenant, roles beyond admin, or queues/workers.

Anti-goals

- Committing the `.sqlite` file or `.env`.
- Data that looks fake (uniform noise, impossible ratios, stale dates).
- A dashboard that looks like the untouched starter kit.

## 3. Approaches considered

| Approach              | Trade-off                                                  | Verdict                    |
| --------------------- | ---------------------------------------------------------- | -------------------------- |
| Render free, Docker   | Real server, writes work; sleeps after ~15 min idle        | **Picked**                 |
| Vercel + `vercel-php` | Free, but read-only disk, SQLite writes and sessions flaky | Rejected                   |
| Laravel Cloud         | Best Laravel fit, but paid after a 14-day credit           | Documented as upgrade path |

Data portability: commit migrations, factories and seeders, never the DB.
Seeding uses a fixed Faker seed and dates relative to "today", so every
clone and every Render boot produces identical, current-looking data.

## 4. Domain model (SQLite)

`users` (+ `is_admin` bool). Gate `view-analytics` → `is_admin`.

`visits` — one row per session

- `session_uuid` uuid unique, `landing_page` string, `source` TrafficSource
  (direct, organic, social, referral, email), `referrer_host` nullable,
  `device` Device (desktop, mobile, tablet), `country_code` char(2),
  `page_views` small int, `duration_seconds` int, `bounced` bool,
  `started_at` datetime (indexed).

`vet_searches`

- `visit_id` FK nullable (null on delete), `search_type` SearchType
  (place, location), `query` nullable (null for location searches),
  `country_code`, `results_count`, `latency_ms`, `status` SearchStatus
  (ok, no_results, location_not_found, upstream_timeout,
  upstream_unavailable, validation_error) — mirrors the site's real error
  codes, `searched_at` datetime (indexed).

`clinics` — `name`, `city`, `country_code`, `lat`, `lon`, `phone` nullable.

`clinic_leads` — a visitor clicked "Open in Maps" or "Call" on a clinic card

- `clinic_id` FK cascade, `vet_search_id` FK nullable, `action` LeadAction
  (directions, call), `status` LeadStatus (new, contacted, converted, lost),
  `occurred_at` datetime (indexed).

`supporters` — Ko-fi: `name` nullable, `amount_cents`, `currency`,
`message` nullable, `is_monthly`, `donated_at`.

All enums are PHP backed enums with `label()`; models cast to them.

## 5. Demo data

- 180 days (so a 90-day range has a previous period to compare against).
- Visits ~80–240/day with weekly seasonality and gentle growth.
- ~55% of visits search (1–2 searches), ~4% of searches fail, status mix
  realistic; place searches carry realistic city / clinic queries.
- ~20% of successful searches produce a clinic lead; older leads have
  progressed through statuses, recent ones are mostly `new`.
- ~40 clinics across PH/US/UK/AU/CA; ~60 supporters.
- Admin: `ADMIN_EMAIL` / `ADMIN_PASSWORD` (defaults
  `admin@plugnpaw.test` / `password`), verified.
- Seeders bulk-insert in chunks inside a transaction (fast); factories
  exist for every model and are used by tests.

## 6. Pages (all behind `auth`, `verified`, `can:view-analytics`)

Every page takes `?range=7d|30d|90d` (default 30d, validated by a Form
Request) and shows deltas vs the previous equal-length period.

- **Overview** `/dashboard` — KPIs (visits, searches, clinic leads, lead
  rate), visits-vs-searches daily line, traffic sources bar, top clinics,
  recent leads.
- **Traffic** `/traffic` — KPIs (visits, bounce rate, avg session),
  daily line, sources, devices, top countries, landing pages.
- **Searches** `/searches` — KPIs (searches, success rate, p95 latency,
  location share), daily stacked bar by outcome, top queries, paginated
  filterable log (status, type).
- **Leads** `/leads` — status KPIs, filterable paginated table (status,
  action, clinic name), inline status change (PATCH, Form Request,
  policy), CSV export (streamed).
- **Clinics** `/clinics` — ranking table: leads, converted, conversion rate.
- **Supporters** `/supporters` — total raised, supporters, monthly share,
  monthly bar, table.

Root `/` redirects to the dashboard (guests land on login).

## 7. Code structure

- `app/Enums/*` backed enums; `app/Support/DateRange` value object built
  from a `Period` enum (`7d`, `30d`, `90d`), with `previous()`.
- `app/Queries/Analytics/*Query` classes: one per page, return plain
  arrays; daily series zero-filled. Controllers stay thin.
- Form Requests for range/filters and lead updates; `ClinicLeadPolicy`.
- `app/Console/Commands/DemoSeedCommand` (`demo:seed`, confirms in
  production unless `--force`).
- `config/demo.php`: `enabled` (`DEMO_MODE`), admin credentials.
  `PreventDemoChanges` middleware blocks profile/password/2FA/passkey/
  delete-account changes when enabled, with a toast.
- Wayfinder route helpers on the frontend (no hard-coded URLs).

## 8. Front end

- Brand from the site: paper background, pine-teal structure, pumpkin-clay
  accent, Fredoka headings, Inter body. Light + dark.
- Charts: Chart.js via `vue-chartjs`; colors from CSS tokens, validated
  with the dataviz palette script; hover tooltips; legends for ≥2 series;
  one axis per chart; status colors reserved for search outcomes.
- Tables: shared `DataTable`-style components, Inertia pagination,
  filters in one row above content, empty states that say what to do.
- Login page shows demo credentials when `DEMO_MODE=true`, plus a
  "may take ~30s to wake" note.

## 9. Quality gates

Pest feature tests (auth, admin gate, every page's props, lead update +
validation, CSV export, demo command, demo-mode blocking), unit tests
(DateRange, query math). Pint, Larastan (level 7), `vp check`,
`vue-tsc`. GitHub Actions runs `composer ci:check`.

## 10. Hosting

`Dockerfile` (multi-stage: build assets with PHP + Node because Wayfinder
needs artisan, then FrankenPHP runtime), `docker/entrypoint.sh` (cache
config, `demo:seed --force`, serve on `$PORT`), `render.yaml` blueprint
(free plan, health check `/up`, `APP_KEY` set in dashboard).
Trust proxies so HTTPS URLs are correct behind Render.

## 11. Calls made by the builder (not user decisions)

Chart.js; 180-day seed; is_admin bool over a roles table; sign-up off;
reset mail to log driver; FrankenPHP runtime; lead statuses
new/contacted/converted/lost.
