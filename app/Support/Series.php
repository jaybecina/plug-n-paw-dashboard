<?php

namespace App\Support;

use Illuminate\Support\Collection;
use stdClass;

final class Series
{
    /**
     * One row per day in the range, with missing days filled with zeros.
     *
     * @param  Collection<int, stdClass>  $rows  rows with a `day` column (Y-m-d)
     * @param  list<string>  $fields
     * @return list<array<string, int|string>>
     */
    public static function daily(DateRange $range, Collection $rows, array $fields): array
    {
        $byDay = $rows->keyBy('day');

        return array_map(function (string $day) use ($byDay, $fields) {
            $row = ['date' => $day];

            foreach ($fields as $field) {
                $row[$field] = (int) ($byDay->get($day)->{$field} ?? 0);
            }

            return $row;
        }, $range->days());
    }
}
