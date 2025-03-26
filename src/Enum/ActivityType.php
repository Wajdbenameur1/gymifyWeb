<?php

namespace App\Enum;

enum ActivityType: string
{
    case PERSONAL_TRAINING = 'Personal Training';
    case GROUP_ACTIVITY = 'Group Activity';
    case FITNESS_CONSULTATION = 'Fitness Consultation';

    public function label(): string
    {
        return match ($this) {
            self::PERSONAL_TRAINING => 'Personal Training',
            self::GROUP_ACTIVITY => 'Group Activity',
            self::FITNESS_CONSULTATION => 'Fitness Consultation',
        };
    }
}
