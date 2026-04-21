<?php

namespace App\Enum;

enum TypeFlux: string
{
    case CF = 'CF'; // Cross-flow
    case RT = 'RT'; // Retour

    public function label(): string
    {
        return match($this) {
            self::CF => 'Cross-flow',
            self::RT => 'Retour',
        };
    }
}
