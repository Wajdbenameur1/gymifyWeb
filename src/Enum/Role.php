<?php
namespace App\Enum;

enum Role: string
{
    case SPORTIF = 'sportif';
    case ENTRAINEUR = 'entraineur';
    case ADMIN = 'admin';
    case RESPONSABLE_SALLE = 'responsable_salle';
    case UTILISATEUR = 'utilisateur';
    public function label(): string
    {
        return match($this) {
            self::SPORTIF => 'Sportif',
            self::ENTRAINEUR => 'Entraîneur',
            self::RESPONSABLE_SALLE => 'Responsable de salle',
            self::ADMIN => 'Admin',
        };
    }
}

