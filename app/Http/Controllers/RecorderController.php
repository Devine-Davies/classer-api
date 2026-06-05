<?php

namespace App\Http\Controllers;

use App\Enums\RecorderCodes;
use App\Models\RecorderModel;

enum RecorderType: int
{
    case USER = 1;
    case SYSTEM = 2;
}

class RecorderController extends Controller
{
    /**
     * Send an email to an admin with analytics report.
     */
    public static function create($uid, $type, $code, $metadata = null)
    {
        $event = new RecorderModel;
        $event->uid = $uid;
        $event->type = $type;
        $event->code = $code;
        $event->metadata = json_encode($metadata);
        $event->save();
    }

    /**
     * Get login metadata
     */
    public static function getLoginMetadata()
    {
        return [
            'ip' => request()->ip(),
            'headers' => request()->headers->all(),
            'user_agent' => request()->userAgent(),
        ];
    }

    /**
     * Login event
     */
    public static function login($uid)
    {
        self::create(
            $uid,
            RecorderType::USER,
            RecorderCodes::USER_LOGIN,
            metadata: self::getLoginMetadata()
        );
    }

    /**
     * Login event
     */
    public static function autoLogin($uid)
    {
        self::create(
            $uid,
            RecorderType::USER,
            RecorderCodes::USER_AUTO_LOGIN,
            metadata: self::getLoginMetadata()
        );
    }

    /**
     * Password reset triggered
     */
    public static function passwordResetTriggered($uid)
    {
        self::create(
            $uid,
            RecorderType::USER,
            RecorderCodes::USER_PASSWORD_RESET_TRIGGERED
        );
    }

    /**
     * User update
     */
    public static function userUpdated($uid)
    {
        self::create(
            $uid,
            RecorderType::USER,
            RecorderCodes::USER_UPDATED
        );
    }
}
