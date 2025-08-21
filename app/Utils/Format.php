<?php

namespace App\Utils;

class Format
{
    public static function niceBytes($bytes, $decimals = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf('%.' . $decimals . 'f %s', $bytes / pow(1024, $factor), $units[$factor]);
    }
}
