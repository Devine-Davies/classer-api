<?php

namespace App\Enums;

enum CloudEntityStatus: string
{
    case PENDING = 'pending';
    case UPLOADING = 'uploading';
    case VALIDATING = 'validating';
    case ACTIVE = 'active';
    case INVALID = 'invalid';
    case SCHEDULED_FOR_DELETION = 'scheduled_for_deletion';
    case DELETED = 'deleted';
}
