<?php

namespace App\Enums;

enum CloudEntityRole: string
{
    case VIDEO = 'video';
    case THUMBNAIL = 'thumbnail';
    case METADATA = 'metadata';
    case TELEMETRY = 'telemetry';
    case SUBTITLE = 'subtitle';
    case PREVIEW = 'preview';
}
