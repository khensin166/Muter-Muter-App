<?php

namespace App\Enums;

enum TourType: string
{
    case ONE_DAY = 'ONE_DAY';
    case MULTI_DAY = 'MULTI_DAY';
    case OPEN_TRIP = 'OPEN_TRIP';
    case PRIVATE_TRIP = 'PRIVATE_TRIP';
    case CUSTOM_TRIP = 'CUSTOM_TRIP';

    public function label(): string
    {
        if (app()->getLocale() === 'en') {
            return match ($this) {
                self::ONE_DAY => 'One Day Trip',
                self::MULTI_DAY => 'Multi-day Trip',
                self::OPEN_TRIP => 'Open Trip (Shared)',
                self::PRIVATE_TRIP => 'Private Trip',
                self::CUSTOM_TRIP => 'Custom Trip',
            };
        }

        return match ($this) {
            self::ONE_DAY => 'One Day Trip',
            self::MULTI_DAY => 'Multi-day Trip',
            self::OPEN_TRIP => 'Open Trip (Gabungan)',
            self::PRIVATE_TRIP => 'Private Trip (Privat)',
            self::CUSTOM_TRIP => 'Custom Trip',
        };
    }
}
