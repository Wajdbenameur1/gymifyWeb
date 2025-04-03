<?php

namespace App\Enum;

enum Role: string
{
    case ADMIN = 'ROLE_ADMIN';
    case RESPONSABLE_SALLE = 'ROLE_RESPONSABLE_SALLE';
    case ENTRAINEUR = 'ROLE_ENTRAINEUR';
    case SPORTIF = 'ROLE_SPORTIF';

    public function label(): string
    {
        // Retourne une version plus lisible du rôle
        return match ($this) {
            self::ADMIN => 'Administrateur',
            self::RESPONSABLE_SALLE => 'Responsable de Salle',
            self::ENTRAINEUR => 'Entraineur',
            self::SPORTIF => 'Sportif',
        };
    }
}
