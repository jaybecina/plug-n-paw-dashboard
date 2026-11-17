<?php

namespace App\Enums;

enum SearchType: string
{
    case Place = 'place';
    case Location = 'location';

    public function label(): string
    {
        return match ($this) {
            self::Place => 'Typed search',
            self::Location => 'Use my location',
        };
    }
}
