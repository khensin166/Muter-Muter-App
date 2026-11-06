<?php

namespace App\Enums;

enum UserRole: string
{
    case CUSTOMER = 'CUSTOMER';
    case PARTNER = 'PARTNER';
    case ADMIN = 'ADMIN';
    case SUPER_ADMIN = 'SUPER_ADMIN';
}
