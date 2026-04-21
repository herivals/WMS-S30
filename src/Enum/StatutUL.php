<?php

namespace App\Enum;

enum StatutUL: string
{
    case DISPONIBLE = 'disponible';
    case RESERVE    = 'réservé';
    case BLOQUE     = 'bloqué';
    case REBUT      = 'rebut';

    public function label(): string
    {
        return match($this) {
            self::DISPONIBLE => 'Disponible',
            self::RESERVE    => 'Réservé',
            self::BLOQUE     => 'Bloqué',
            self::REBUT      => 'Rebut',
        };
    }
}
