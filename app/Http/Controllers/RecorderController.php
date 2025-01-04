<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RecorderModel;
use App\Enums\RecorderCodes;

enum RecorderType: int {
    case USER = 1;
    case SYSTEM = 2;
}

class RecorderController extends Controller
{
    /**
     * Send an email to an admin with analytics report.
     */
    static public function create($uid, $type, $code, $metadata = null)
    {
        $event = new RecorderModel();
        $event->uid = $uid;
        $event->type = $type;
        $event->code = $code;
        $event->metadata = json_encode($metadata);
        $event->save();
    }

    /**
     * Get login metadata
     */
    static public function getLoginMetadata()
    {
        return [
            'ip' => request()->ip(),
            'user_agent' => request()->header('User-Agent')
        ];
    }

    /**
     * Login event
     */
    static public function login($uid)
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
    static public function autoLogin($uid)
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
    static public function passwordResetTriggered($uid)
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
    static public function userUpdated($uid)
    {
        self::create(
            $uid, 
            RecorderType::USER,
            RecorderCodes::USER_UPDATED
        );
    }
}
