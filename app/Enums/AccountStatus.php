<?php

namespace App\Enums;

enum AccountStatus: int {
    case VERIFIED = 1;
    case INACTIVE = 0;
    case SUSPENDED = 2;
    case DEACTIVATED = 3;
}