<?php

namespace App\Enums;

enum TransmissionType: string
{
    case MANUAL = 'MANUAL';
    case AUTOMATIC = 'AUTOMATIC';

    public function label(): string
    {
        if (app()->getLocale() === 'en') {
            return match ($this) {
                self::MANUAL => 'Manual',
                self::AUTOMATIC => 'Automatic',
            };
        }

        return match ($this) {
            self::MANUAL => 'Manual',
            self::AUTOMATIC => 'Otomatis',
        };
    }
}
