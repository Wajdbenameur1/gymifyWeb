<?php
<<<<<<< HEAD

namespace App\Enum;

enum Role: string {
    case ADMIN = 'admin';
    case ENTRAINEUR = 'entraineur';
    case RESPONSABLE_SALLE = 'responsable_salle';
    case SPORTIF = 'sportif';
}
=======
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

>>>>>>> 1e2a521f379c042fb627b82253dcd3e5a8f8a1fc
