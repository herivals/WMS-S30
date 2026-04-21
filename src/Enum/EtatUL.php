<?php

namespace App\Enum;

enum EtatUL: string
{
    case GOOD = 'GOOD';
    case HS   = 'HS';

    public function label(): string
    {
        return match($this) {
            self::GOOD => 'Bon état',
            self::HS   => 'Hors service',
        };
    }
}
