<?php

namespace App\Enums;

enum AccountStatus: int
{
    case INACTIVE = 0;
    case VERIFIED = 1;
    case SUSPENDED = 2;
    case DEACTIVATED = 3;
}
