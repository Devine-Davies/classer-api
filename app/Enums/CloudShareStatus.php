<?php

namespace App\Enums;

enum CloudShareStatus: int
{
    case PENDING = 1;
    case UPLOADING = 2;
    case VALIDATING = 3;
    case ACTIVE = 4;
    case FAILED = 5;
    case CLEANING = 6;
}
