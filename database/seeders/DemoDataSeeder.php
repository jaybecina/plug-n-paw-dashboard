<?php

namespace Database\Seeders;

use App\Enums\Device;
use App\Enums\LeadAction;
use App\Enums\LeadStatus;
use App\Enums\SearchStatus;
use App\Enums\SearchType;
use App\Enums\TrafficSource;
use Carbon\CarbonImmutable;
use Database\Seeders\Demo\Catalog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Random\Engine\Mt19937;
use Random\Randomizer;

/**
 * Builds 180 days of believable, fully deterministic website activity.
 *
 * A private, seeded random engine (not PHP's global mt_rand, which other
 * code may touch) and a "today" anchor always produce the same rows, so
 * every clone of the repo (and every Render boot) shows identical numbers.
 * Rows are generated as plain arrays and bulk-inserted: the factories are
 * kept for tests, where expressiveness matters more than speed.
 */
class DemoDataSeeder extends Seeder
{
    public const SEED = 2026;

    public const DAYS = 180;

    private const CHUNK = 500;

    private Randomizer $random;

    private CarbonImmutable $now;

    /** @var array<string, list<array{id: int, weight: int}>> */
    private array $clinicsByCountry = [];

    /** @var list<string> */
    private array $clinicNames = [];

    /** @var list<array<string, mixed>> */
    private array $visits = [];

    /** @var list<array<string, mixed>> */
    private array $searches = [];

    /** @var list<array<string, mixed>> */
    private array $leads = [];

    public function run(): void
    {
        $this->random = new Randomizer(new Mt19937(self::SEED));
        $this->now = CarbonImmutable::now()->startOfMinute();

        DB::transaction(function () {
            $this->seedClinics();
            $this->generateActivity();
            $this->insert('visits', $this->visits);
            $this->insert('vet_searches', $this->searches);
            $this->insert('clinic_leads', $this->leads);
            $this->seedSupporters();
        });
    }

    private function seedClinics(): void
    {
        $rows = [];
        $nextId = (int) DB::table('clinics')->max('id') + 1;
        $prefixes = Catalog::CLINIC_PREFIXES;
        $types = Catalog::CLINIC_TYPES;

        foreach (Catalog::COUNTRIES as $country => $config) {
            foreach ($config['cities'] as $city => [$lat, $lon]) {
                $perCity = $country === 'PH' ? 3 : 2;

                for ($i = 0; $i < $perCity; $i++) {
                    $name = $prefixes[($nextId * 7) % count($prefixes)].' '.$types[($nextId * 5) % count($types)];

                    $rows[] = [
                        'id' => $nextId,
                        'name' => $name,
                        'city' => $city,
                        'country_code' => $country,
                        'lat' => round($lat + $this->float(-0.04, 0.04), 6),
                        'lon' => round($lon + $this->float(-0.04, 0.04), 6),
                        'phone' => $this->chance(85) ? $this->digits('+## ### ### ####') : null,
                        'created_at' => $this->now->subDays(self::DAYS)->toDateTimeString(),
                        'updated_at' => $this->now->subDays(self::DAYS)->toDateTimeString(),
                    ];

                    // Earlier clinics in each country get more clicks (long tail).
                    $this->clinicsByCountry[$country][] = ['id' => $nextId, 'weight' => max(1, 12 - count($this->clinicsByCountry[$country] ?? []) * 2)];
                    $this->clinicNames[] = $name;
                    $nextId++;
                }
            }
        }

        $this->insert('clinics', $rows);
    }

    private function generateActivity(): void
    {
        $visitId = (int) DB::table('visits')->max('id') + 1;
        $searchId = (int) DB::table('vet_searches')->max('id') + 1;

        // Days with something unusual, counted back from today.
        $socialSpikes = [12, 47, 118];
        $providerOutage = 23;

        for ($daysAgo = self::DAYS - 1; $daysAgo >= 0; $daysAgo--) {
            $day = $this->now->subDays($daysAgo)->startOfDay();
            $isToday = $daysAgo === 0;
            $secondsAvailable = $isToday ? max(60, (int) $day->diffInSeconds($this->now)) : 86_399;
            $isSpike = in_array($daysAgo, $socialSpikes, true);

            $count = $this->visitsFor($day, $daysAgo, $isSpike);

            if ($isToday) {
                $count = (int) round($count * $secondsAvailable / 86_400);
            }

            for ($i = 0; $i < $count; $i++) {
                $startedAt = $day->addSeconds($this->random->getInt(0, $secondsAvailable - 1));
                $visit = $this->makeVisit($visitId, $startedAt, $isSpike);
                $this->visits[] = $visit;

                foreach ($this->makeSearches($visit, $startedAt, $searchId, $daysAgo === $providerOutage) as $search) {
                    $this->searches[] = $search;
                    $this->makeLeads($search, $daysAgo);
                    $searchId++;
                }

                $visitId++;
            }
        }
    }

    /**
     * Gentle growth, a weekend lift, day-to-day noise and the odd viral post.
     */
    private function visitsFor(CarbonImmutable $day, int $daysAgo, bool $isSpike): int
    {
        $growth = 90 + (self::DAYS - $daysAgo) * 0.6;
        $weekday = match ($day->dayOfWeekIso) {
            6, 7 => 1.22,
            1 => 1.08,
            default => 1.0,
        };
        $noise = $this->float(0.85, 1.15);

        return (int) round($growth * $weekday * $noise * ($isSpike ? 2.4 : 1));
    }

    /**
     * @return array<string, mixed>
     */
    private function makeVisit(int $id, CarbonImmutable $startedAt, bool $isSpike): array
    {
        $source = $isSpike && $this->chance(55)
            ? TrafficSource::Social
            : TrafficSource::from($this->weighted(['organic' => 42, 'direct' => 25, 'social' => 15, 'referral' => 12, 'email' => 6]));

        $landing = $source === TrafficSource::Organic
            ? $this->weighted(['/find-vet' => 55, '/' => 45])
            : $this->weighted(['/' => 62, '/find-vet' => 38]);

        $bounced = $this->chance($landing === '/' ? 44 : 27);

        return [
            'id' => $id,
            'session_uuid' => $this->uuid(),
            'landing_page' => $landing,
            'source' => $source->value,
            'referrer_host' => isset(Catalog::REFERRERS[$source->value]) ? $this->pick(Catalog::REFERRERS[$source->value]) : null,
            'device' => $this->weighted([Device::Mobile->value => 62, Device::Desktop->value => 32, Device::Tablet->value => 6]),
            'country_code' => $this->weighted(array_map(fn (array $c) => $c['weight'], Catalog::COUNTRIES)),
            'page_views' => $bounced ? 1 : $this->random->getInt(2, 7),
            'duration_seconds' => $bounced ? $this->random->getInt(4, 40) : $this->random->getInt(45, 620),
            'bounced' => $bounced,
            'started_at' => $startedAt->toDateTimeString(),
        ];
    }

    /**
     * @param  array<string, mixed>  $visit
     * @return list<array<string, mixed>>
     */
    private function makeSearches(array $visit, CarbonImmutable $startedAt, int $nextId, bool $outage): array
    {
        $searchChance = $visit['bounced'] ? ($visit['landing_page'] === '/find-vet' ? 30 : 0) : 72;

        if (! $this->chance($searchChance)) {
            return [];
        }

        $count = $visit['bounced'] ? 1 : (int) $this->weighted(['1' => 70, '2' => 25, '3' => 5]);
        $country = $visit['country_code'];
        $cities = array_keys(Catalog::COUNTRIES[$country]['cities']);
        $rows = [];

        for ($i = 0; $i < $count; $i++) {
            $type = $this->chance($visit['device'] === Device::Mobile->value ? 44 : 26)
                ? SearchType::Location
                : SearchType::Place;

            $status = $this->searchStatus($type, $outage);
            $searchedAt = $startedAt->addSeconds(min($visit['duration_seconds'], 8 + $i * 40 + $this->random->getInt(0, 30)));

            $rows[] = [
                'id' => $nextId + $i,
                'visit_id' => $visit['id'],
                'search_type' => $type->value,
                'query' => $type === SearchType::Place ? $this->query($this->pick($cities)) : null,
                'country_code' => $country,
                'results_count' => $status === SearchStatus::Ok ? $this->random->getInt(3, 20) : 0,
                'latency_ms' => $this->latency($status),
                'status' => $status->value,
                'searched_at' => min($searchedAt, $this->now)->toDateTimeString(),
            ];
        }

        return $rows;
    }

    private function searchStatus(SearchType $type, bool $outage): SearchStatus
    {
        if ($outage && $this->chance(38)) {
            return SearchStatus::UpstreamUnavailable;
        }

        $weights = [
            SearchStatus::Ok->value => 885,
            SearchStatus::NoResults->value => 60,
            SearchStatus::LocationNotFound->value => $type === SearchType::Place ? 25 : 0,
            SearchStatus::UpstreamTimeout->value => 18,
            SearchStatus::UpstreamUnavailable->value => 7,
            SearchStatus::ValidationError->value => $type === SearchType::Place ? 5 : 0,
        ];

        return SearchStatus::from($this->weighted($weights));
    }

    private function latency(SearchStatus $status): int
    {
        return match ($status) {
            SearchStatus::Ok, SearchStatus::NoResults, SearchStatus::LocationNotFound =>
                // Right-skewed: most answers are quick, a few are slow.
                (int) round(160 + 260 * (-log(max(0.0001, $this->float(0, 1))))),
            SearchStatus::UpstreamTimeout => 8_000,
            SearchStatus::UpstreamUnavailable => $this->random->getInt(40, 260),
            SearchStatus::ValidationError => $this->random->getInt(3, 18),
        };
    }

    private function query(string $city): string
    {
        $pattern = $this->weighted(Catalog::QUERY_PATTERNS);

        return strtr($pattern, [
            '{city}' => $city,
            '{clinic}' => $this->pick($this->clinicNames),
        ]);
    }

    /**
     * @param  array<string, mixed>  $search
     */
    private function makeLeads(array $search, int $daysAgo): void
    {
        if ($search['status'] !== SearchStatus::Ok->value || ! $this->chance(22)) {
            return;
        }

        $clinics = $this->clinicsByCountry[$search['country_code']];
        $weights = [];
        foreach ($clinics as $clinic) {
            $weights[(string) $clinic['id']] = $clinic['weight'];
        }

        $occurredAt = CarbonImmutable::parse($search['searched_at'])->addSeconds($this->random->getInt(5, 120));

        $this->leads[] = [
            'clinic_id' => (int) $this->weighted($weights),
            'vet_search_id' => $search['id'],
            'action' => $this->weighted([LeadAction::Directions->value => 72, LeadAction::Call->value => 28]),
            'status' => $this->leadStatus($daysAgo),
            'occurred_at' => min($occurredAt, $this->now)->toDateTimeString(),
            'updated_at' => min($occurredAt, $this->now)->toDateTimeString(),
        ];
    }

    /**
     * Older leads have had time to move through the pipeline.
     */
    private function leadStatus(int $daysAgo): string
    {
        $weights = match (true) {
            $daysAgo <= 2 => ['new' => 85, 'contacted' => 15, 'converted' => 0, 'lost' => 0],
            $daysAgo <= 21 => ['new' => 40, 'contacted' => 36, 'converted' => 15, 'lost' => 9],
            default => ['new' => 6, 'contacted' => 22, 'converted' => 42, 'lost' => 30],
        };

        return LeadStatus::from($this->weighted($weights))->value;
    }

    private function seedSupporters(): void
    {
        $rows = [];

        for ($daysAgo = self::DAYS - 1; $daysAgo >= 0; $daysAgo--) {
            // Roughly one supporter every three days, more after social spikes.
            if (! $this->chance(in_array($daysAgo, [11, 46, 117], true) ? 90 : 30)) {
                continue;
            }

            $donatedAt = $this->now->subDays($daysAgo)->startOfDay()->addMinutes($this->random->getInt(0, $daysAgo === 0 ? 0 : 1439));

            $rows[] = [
                'name' => $this->chance(75) ? $this->pick(Catalog::FIRST_NAMES) : null,
                'amount_cents' => (int) $this->weighted(['300' => 30, '500' => 34, '1000' => 20, '1500' => 7, '2500' => 6, '5000' => 3]),
                'currency' => 'USD',
                'message' => $this->chance(55) ? $this->pick(Catalog::SUPPORTER_MESSAGES) : null,
                'is_monthly' => $this->chance(20),
                'donated_at' => min($donatedAt, $this->now)->toDateTimeString(),
            ];
        }

        $this->insert('supporters', $rows);
    }

    private function chance(int $percent): bool
    {
        return $this->random->getInt(1, 100) <= $percent;
    }

    private function float(float $min, float $max): float
    {
        return round($min + ($max - $min) * $this->random->getFloat(0, 1), 4);
    }

    /**
     * @template T
     *
     * @param  array<array-key, T>  $items
     * @return T
     */
    private function pick(array $items): mixed
    {
        $items = array_values($items);

        return $items[$this->random->getInt(0, count($items) - 1)];
    }

    private function uuid(): string
    {
        $bytes = $this->random->getBytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0F) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3F) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4));
    }

    private function digits(string $pattern): string
    {
        return (string) preg_replace_callback('/#/', fn () => (string) $this->random->getInt(0, 9), $pattern);
    }

    /**
     * Pick a key from a [key => weight] map using the seeded engine.
     *
     * @param  array<array-key, int>  $weights
     */
    private function weighted(array $weights): string
    {
        $roll = $this->random->getInt(1, array_sum($weights));

        foreach ($weights as $key => $weight) {
            $roll -= $weight;

            if ($roll <= 0) {
                return (string) $key;
            }
        }

        return (string) array_key_last($weights);
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     */
    private function insert(string $table, array $rows): void
    {
        foreach (array_chunk($rows, self::CHUNK) as $chunk) {
            DB::table($table)->insert($chunk);
        }
    }
}
