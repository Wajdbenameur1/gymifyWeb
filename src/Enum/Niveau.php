<?php

namespace App\Enum;

enum Niveau: string
{
    case DEBUTANT = 'DEBUTANT';
    case INTERMEDIAIRE = 'INTERMEDIAIRE';
    case AVANCE = 'AVANCE';
    case PROFESSIONNEL = 'PROFESSIONNEL';

    public function label(): string
    {
        return match($this) {
            self::DEBUTANT => 'Beginner',
            self::INTERMEDIAIRE => 'Intermediate',
            self::AVANCE => 'Advanced',
            self::PROFESSIONNEL => 'Professional',
        };
    }
}
