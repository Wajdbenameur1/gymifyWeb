<?php

namespace App\Enum;

enum Role: string {
    case ADMIN = 'admin';
    case ENTRAINEUR = 'entraineur';
    case RESPONSABLE_SALLE = 'responsable_salle';
    case SPORTIF = 'sportif';
}
