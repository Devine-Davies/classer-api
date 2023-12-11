<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeEmail;
use App\Mail\LoginReminder;
use App\Mail\AdminAnalyticsReport;

class MailSenderController extends Controller
{
    /**
     * Send an email to an admin with analytics report.
     */
    static public function sendAdminAnalyticsReport($data)
    {
        Mail::to('info@classermedia.com')->send(
            new AdminAnalyticsReport($data)
        );
    }

    /**
     * Send an email to a user who has not logged in yet.
     */
    static public function sendCode($user)
    {
        Mail::to($user['email'])->send(
            new WelcomeEmail('Welcome to Classer', $user)
        );
    }

    /**
     * Send an email to a user who has not logged in yet.
     */
    static public function resendCode($user)
    {
        Mail::to($user['email'])->send(
            new WelcomeEmail('Code Reminder', $user)
        );
    }

    /**
     * Send an email to a user who has not logged in yet.
     */
    static public function SendAutoLoginReminder($subject, $user)
    {
        Mail::to($user['email'])->send(
            new LoginReminder($subject, $user)
        );
    }
}
