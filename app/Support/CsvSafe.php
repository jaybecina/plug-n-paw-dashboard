<?php

namespace App\Support;

final class CsvSafe
{
    /**
     * Prefix values a spreadsheet would run as a formula (CSV injection).
     */
    public static function cell(string|int|float|null $value): string|int|float|null
    {
        if (is_string($value) && $value !== '' && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "'".$value;
        }

        return $value;
    }
}
