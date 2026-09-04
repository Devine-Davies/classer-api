<?php

namespace App\Exceptions;

use RuntimeException;

class CloudStorageQuotaExceededException extends RuntimeException
{
    public function __construct(private readonly int $attemptedBytes)
    {
        parent::__construct('Cloud storage quota exceeded.');
    }

    public function attemptedBytes(): int
    {
        return $this->attemptedBytes;
    }
}
