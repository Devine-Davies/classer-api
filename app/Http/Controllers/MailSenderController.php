<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeEmail;
use App\Mail\LoginReminder;

class MailSenderController extends Controller
{
    static public function sendTrialCode($user)
    {
        Mail::to($user->email)->send(
            new WelcomeEmail($user)
        );
    }

    /**
     * Send an email to a user who has not logged in yet.
     */
    static public function SendAutoLoginReminder($user)
    {
        Mail::to($user->email)->send(
            new LoginReminder($user)
        );
    }
}
