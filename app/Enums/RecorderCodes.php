<?php

namespace App\Enums;

enum RecorderCodes: int
{
    case USER_LOGIN = 201;
    case USER_AUTO_LOGIN = 202;
    case USER_UPDATED = 210;
    case USER_PASSWORD_RESET_TRIGGERED = 220;
}
