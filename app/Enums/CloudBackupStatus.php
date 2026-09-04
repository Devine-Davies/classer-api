<?php

namespace App\Enums;

enum CloudBackupStatus: int
{
    case PENDING = 1;
    case UPLOADING = 2;
    case VALIDATING = 3;
    case ACTIVE = 4;
    case FAILED = 5;
    case RESTORING = 6;
    case SCHEDULED_FOR_DELETION = 7;
    case DELETED = 8;
}
