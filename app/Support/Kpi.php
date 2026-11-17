<?php

namespace App\Support;

/**
 * Shapes a headline number and its previous-period comparison for the UI.
 */
final class Kpi
{
    /**
     * @param  'number'|'percent'|'duration'|'currency'|'ms'  $format
     * @return array{key: string, label: string, value: int|float, previous: int|float, delta: float|null, format: string, higherIsBetter: bool}
     */
    public static function make(
        string $key,
        string $label,
        int|float $value,
        int|float $previous,
        string $format = 'number',
        bool $higherIsBetter = true,
    ): array {
        return [
            'key' => $key,
            'label' => $label,
            'value' => $value,
            'previous' => $previous,
            'delta' => Delta::percent($value, $previous),
            'format' => $format,
            'higherIsBetter' => $higherIsBetter,
        ];
    }

    /**
     * A share as a percentage with one decimal, 0 when the whole is empty.
     */
    public static function share(int|float $part, int|float $whole): float
    {
        return $whole > 0 ? round($part / $whole * 100, 1) : 0.0;
    }
}
