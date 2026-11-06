<?php

namespace App\Enums;

enum RefundStatus: string
{
    case PENDING = 'PENDING';
    case APPROVED = 'APPROVED';
    case PROCESSED = 'PROCESSED';
    case REJECTED = 'REJECTED';
}

