<?php

namespace App\Enums;

enum VehicleType: string
{
    case CAR = 'CAR';
    case MINIBUS = 'MINIBUS';
    case BUS = 'BUS';
    case BOAT = 'BOAT';

    public function label(): string
    {
        if (app()->getLocale() === 'en') {
            return match ($this) {
                self::CAR => 'MPV / SUV Car',
                self::MINIBUS => 'Minibus (HiAce / Elf)',
                self::BUS => 'Medium / Big Bus',
                self::BOAT => 'Lake Tour Boat',
            };
        }

        return match ($this) {
            self::CAR => 'Mobil MPV / SUV',
            self::MINIBUS => 'Minibus (HiAce / Elf)',
            self::BUS => 'Medium / Big Bus',
            self::BOAT => 'Kapal Wisata Danau',
        };
    }
}
