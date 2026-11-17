<?php

namespace App\Enums;

/**
 * Mirrors the outcomes of the website's /api/vets/search route.
 */
enum SearchStatus: string
{
    case Ok = 'ok';
    case NoResults = 'no_results';
    case LocationNotFound = 'location_not_found';
    case UpstreamTimeout = 'upstream_timeout';
    case UpstreamUnavailable = 'upstream_unavailable';
    case ValidationError = 'validation_error';

    public function label(): string
    {
        return match ($this) {
            self::Ok => 'Results found',
            self::NoResults => 'No clinics found',
            self::LocationNotFound => 'Place not found',
            self::UpstreamTimeout => 'Map provider timed out',
            self::UpstreamUnavailable => 'Map provider down',
            self::ValidationError => 'Invalid input',
        };
    }

    /**
     * The search reached the map provider and returned an answer.
     */
    public function isSuccess(): bool
    {
        return in_array($this, [self::Ok, self::NoResults], true);
    }

    /**
     * @return list<self>
     */
    public static function failures(): array
    {
        return array_values(array_filter(self::cases(), fn (self $status) => ! $status->isSuccess()));
    }
}
