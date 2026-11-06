<?php

namespace App\Enums;

enum SettlementStatus: string
{
    case PENDING = 'PENDING';
    case ELIGIBLE = 'ELIGIBLE';
    case PROCESSING = 'PROCESSING';
    case PAID = 'PAID';
    case ON_HOLD = 'ON_HOLD';
}

