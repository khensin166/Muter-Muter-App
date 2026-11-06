<?php

namespace App\Enums;

enum PartnerStatus: string
{
    case PENDING = 'PENDING';
    case APPROVED = 'APPROVED';
    case SUSPENDED = 'SUSPENDED';
    case REJECTED = 'REJECTED';
}

