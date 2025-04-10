<?php
namespace App\Enum;

enum Role: string
{
    case SPORTIF = 'sportif';
    case ADMIN = 'admin';
    case RESPONSABLE_SALLE = 'responsable_salle';
    case ENTRAINEUR = 'entraineur';
}
