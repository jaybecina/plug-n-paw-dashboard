<?php

namespace App\Enums;

enum Period: string
{
    case Week = '7d';
    case Month = '30d';
    case Quarter = '90d';

    public static function default(): self
    {
        return self::Month;
    }

    public function days(): int
    {
        return match ($this) {
            self::Week => 7,
            self::Month => 30,
            self::Quarter => 90,
        };
    }

    public function label(): string
    {
        return "Last {$this->days()} days";
    }
}
