<?php

namespace Database\Seeders\Demo;

/**
 * Fixed reference data for the demo seed. Clinic names are fictional.
 */
final class Catalog
{
    /**
     * Visitor share by country (weights) and the cities searched there.
     *
     * @var array<string, array{weight: int, cities: array<string, array{float, float}>}>
     */
    public const COUNTRIES = [
        'PH' => ['weight' => 38, 'cities' => [
            'Quezon City' => [14.6760, 121.0437],
            'Makati' => [14.5547, 121.0244],
            'Cebu City' => [10.3157, 123.8854],
            'Davao City' => [7.1907, 125.4553],
            'Pasig' => [14.5764, 121.0851],
        ]],
        'US' => ['weight' => 24, 'cities' => [
            'Austin' => [30.2672, -97.7431],
            'Seattle' => [47.6062, -122.3321],
            'Denver' => [39.7392, -104.9903],
            'San Diego' => [32.7157, -117.1611],
        ]],
        'GB' => ['weight' => 10, 'cities' => [
            'London' => [51.5072, -0.1276],
            'Manchester' => [53.4808, -2.2426],
            'Bristol' => [51.4545, -2.5879],
        ]],
        'AU' => ['weight' => 8, 'cities' => [
            'Melbourne' => [-37.8136, 144.9631],
            'Brisbane' => [-27.4698, 153.0251],
        ]],
        'CA' => ['weight' => 7, 'cities' => [
            'Toronto' => [43.6532, -79.3832],
            'Vancouver' => [49.2827, -123.1207],
        ]],
        'SG' => ['weight' => 5, 'cities' => [
            'Singapore' => [1.3521, 103.8198],
        ]],
        'NZ' => ['weight' => 4, 'cities' => [
            'Auckland' => [-36.8485, 174.7633],
        ]],
    ];

    /**
     * Name parts combined into fictional clinic names.
     *
     * @var list<string>
     */
    public const CLINIC_PREFIXES = [
        'Paw Print', 'Whiskers & Tails', 'Happy Snout', 'Greenleaf', 'Northside',
        'Bayview', 'Furever', 'Little Hooves', 'Kindred', 'Riverbend', 'Cedar Lane',
        'Sunny Paws', 'Harbor', 'Maple Street', 'Tall Tails', 'Good Boy',
    ];

    /**
     * @var list<string>
     */
    public const CLINIC_TYPES = [
        'Veterinary Clinic', 'Animal Hospital', 'Pet Clinic', 'Vet Centre',
        'Animal Care', '24/7 Emergency Vets',
    ];

    /**
     * Free-text search patterns; {city} is replaced.
     *
     * @var array<string, int>
     */
    public const QUERY_PATTERNS = [
        '{city}' => 34,
        'vet near {city}' => 18,
        'emergency vet {city}' => 12,
        '24 hour vet {city}' => 8,
        'cat clinic {city}' => 6,
        'animal hospital {city}' => 8,
        '{clinic}' => 14,
    ];

    /**
     * @var array<string, list<string>>
     */
    public const REFERRERS = [
        'organic' => ['google.com', 'bing.com', 'duckduckgo.com'],
        'social' => ['facebook.com', 'instagram.com', 'reddit.com', 'tiktok.com'],
        'referral' => ['ko-fi.com', 'github.com', 'petowners.community', 'pawsandclaws.blog'],
        'email' => ['mail.google.com'],
    ];

    /**
     * @var list<string>
     */
    public const FIRST_NAMES = [
        'Ana', 'Ben', 'Carla', 'Diego', 'Ella', 'Finn', 'Grace', 'Hiro', 'Isla',
        'Jun', 'Kai', 'Lea', 'Marco', 'Nina', 'Owen', 'Pia', 'Rafa', 'Sam',
        'Tala', 'Uma', 'Vince', 'Wren', 'Yuki', 'Zoe',
    ];

    /**
     * @var list<string>
     */
    public const SUPPORTER_MESSAGES = [
        'Found an emergency vet at 2am thanks to this. Thank you!',
        'Keep it free for everyone 🐾',
        'From a very grateful cat parent.',
        'Saved me so much time after moving cities.',
        'Coffee for the dev!',
        'Our pup is doing great now.',
        'Love how fast the map loads.',
        'Thanks for building this.',
    ];
}
