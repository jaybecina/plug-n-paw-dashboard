<?php

namespace App\Enums;

enum Device: string
{
    case Desktop = 'desktop';
    case Mobile = 'mobile';
    case Tablet = 'tablet';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
