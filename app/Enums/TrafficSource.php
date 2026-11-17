<?php

namespace App\Enums;

enum TrafficSource: string
{
    case Direct = 'direct';
    case Organic = 'organic';
    case Social = 'social';
    case Referral = 'referral';
    case Email = 'email';

    public function label(): string
    {
        return match ($this) {
            self::Direct => 'Direct',
            self::Organic => 'Organic search',
            self::Social => 'Social',
            self::Referral => 'Referral',
            self::Email => 'Email',
        };
    }
}
