<?php

namespace App\Enum;

enum TypeMouvement: string
{
    case ENTREE         = 'entrée';
    case SORTIE         = 'sortie';
    case TRANSFERT      = 'transfert';
    case CHANGEMENT_LOT = 'changement_lot';

    public function label(): string
    {
        return match($this) {
            self::ENTREE         => 'Entrée',
            self::SORTIE         => 'Sortie',
            self::TRANSFERT      => 'Transfert',
            self::CHANGEMENT_LOT => 'Changement lot',
        };
    }
}
