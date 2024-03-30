<?php

namespace App\Utils;

use Illuminate\Support\Str;

class EmailToken
{
    /**
     * Generate a token
     */
    public static function generateToken()
    {
        $now = base64_encode(now());
        $token = Str::random(60);
        $delimiter = '.';
        return $now . $delimiter . $token;
    }

    /**
     * Check if the token has expired
     */
    public static function hasExpired($token)
    {
        $delimiter = '.';
        $tokenParts = explode($delimiter, $token);
        $now = base64_decode($tokenParts[0]);
        $tokenCreatedAt = strtotime($now);
        $tokenExpiry = strtotime('+1 day', $tokenCreatedAt);
        return $tokenExpiry < strtotime(now());
    }
}
