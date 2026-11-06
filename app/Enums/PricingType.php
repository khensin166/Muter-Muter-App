<?php

namespace App\Enums;

enum PricingType: string
{
    case PER_PERSON = 'PER_PERSON';
    case PER_VEHICLE = 'PER_VEHICLE';
    case PER_GROUP = 'PER_GROUP';
}

