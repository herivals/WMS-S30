<?php

namespace App\Enum;

enum TypeReception: string
{
    case STANDARD  = 'standard';
    case RETOUR    = 'retour';
    case TRANSFERT = 'transfert';

    public function label(): string
    {
        return match($this) {
            self::STANDARD  => 'Standard',
            self::RETOUR    => 'Retour',
            self::TRANSFERT => 'Transfert',
        };
    }
}
