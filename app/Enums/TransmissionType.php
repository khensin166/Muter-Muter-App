<?php

namespace App\Enums;

enum TransmissionType: string
{
    case MANUAL = 'MANUAL';
    case AUTOMATIC = 'AUTOMATIC';

    public function label(): string
    {
        return match ($this) {
            self::MANUAL => 'Manual',
            self::AUTOMATIC => 'Automatic',
        };
    }
}
