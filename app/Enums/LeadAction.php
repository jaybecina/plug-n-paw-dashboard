<?php

namespace App\Enums;

enum LeadAction: string
{
    case Directions = 'directions';
    case Call = 'call';

    public function label(): string
    {
        return match ($this) {
            self::Directions => 'Opened in Maps',
            self::Call => 'Tapped to call',
        };
    }
}
