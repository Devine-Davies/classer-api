<?php

namespace App\Enums;

enum CloudStorageKind: string
{
    case SHARE = 'share';
    case BACKUP = 'backup';

    public function usageColumn(): string
    {
        return $this->value.'_usage';
    }

    public function capability(): string
    {
        return 'cloud_'.$this->value;
    }
}
