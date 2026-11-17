<?php

namespace App\Enums;

enum LeadStatus: string
{
    case New = 'new';
    case Contacted = 'contacted';
    case Converted = 'converted';
    case Lost = 'lost';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
