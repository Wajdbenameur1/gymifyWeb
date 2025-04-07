<?php

namespace App\Enum;

enum ActivityType: string
{
    case PERSONAL_TRAINING = 'PERSONAL_TRAINING';
    case GROUP_ACTIVITY = 'GROUP_ACTIVITY';
    case FITNESS_CONSULTATION = 'FITNESS_CONSULTATION';

    public function label(): string
    {
        return match($this) {
            self::PERSONAL_TRAINING => 'Personal Training',
            self::GROUP_ACTIVITY => 'Group Activity',
            self::FITNESS_CONSULTATION => 'Fitness Consultation',
        };
    }
    
}