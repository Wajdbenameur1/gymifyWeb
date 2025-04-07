<?php

namespace App\Enum;

enum ObjectifCours: string
{
    case PERTE_POIDS = 'PERTE_POIDS';
    case PRISE_DE_MASSE = 'PRISE_DE_MASSE';
    case ENDURANCE = 'ENDURANCE';
    case RELAXATION = 'RELAXATION';

    public function label(): string
    {
        return match($this) {
            self::PERTE_POIDS => 'Perte de poids',
            self::PRISE_DE_MASSE => 'Prise de masse ',
            self::ENDURANCE => 'Endurance',
            self::RELAXATION => 'Relaxation',
        };
    }

    
}