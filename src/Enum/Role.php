<?php
namespace App\Enum;

enum Role: string
{
    case SPORTIF = 'sportif';
    case ENTRAINEUR = 'entraineur';
    case ADMIN = 'admin';
    case RESPONSABLE_SALLE = 'responsable_salle';
   
}
