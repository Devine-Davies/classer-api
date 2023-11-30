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
    static public function sendTrialCode($user)
    {
        Mail::to($user['email'])->send(
            new WelcomeEmail($user)
        );
    }

    /**
     * Send an email to a user who has not logged in yet.
     */
    static public function SendAutoLoginReminder($user)
    {
        Mail::to($user['email'])->send(
            new LoginReminder($user)
        );
    }
}
