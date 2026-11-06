<?php

namespace App\Enums;

enum TourType: string
{
    case ONE_DAY = 'ONE_DAY';
    case MULTI_DAY = 'MULTI_DAY';
    case OPEN_TRIP = 'OPEN_TRIP';
    case PRIVATE_TRIP = 'PRIVATE_TRIP';
    case CUSTOM_TRIP = 'CUSTOM_TRIP';
}
