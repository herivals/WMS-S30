<?php

namespace App\Enum;

enum TypeUnite: string
{
    case PALETTE = 'PAL';
    case COLIS   = 'COLIS';
    case UNITE   = 'UNITE';

    public function label(): string
    {
        return match($this) {
            self::PALETTE => 'Palette',
            self::COLIS   => 'Colis',
            self::UNITE   => 'Unité',
        };
    }
}
