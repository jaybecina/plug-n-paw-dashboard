<?php

namespace App\Support;

final class Delta
{
    /**
     * Percentage change from previous to current, or null when there is
     * nothing to compare against.
     */
    public static function percent(int|float $current, int|float $previous): ?float
    {
        if ($previous == 0) {
            return null;
        }

        return round(($current - $previous) / $previous * 100, 1);
    }
}
