<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Mail;
use App\Mail\TemplateOne;
use App\Mail\SuperSimpleEmail;
use App\Mail\AdminAnalyticsReport;

class MailSenderController extends Controller
{
    /**
     * Admin analytics report.
     */
    static public function sendAdminAnalyticsReport($data)
    {
        Mail::to('info@classermedia.com')->send(
            new AdminAnalyticsReport($data)
        );
    }

    /**
     * Verify account email.
     */
    static public function verifyAccount($email, $user)
    {
        $subject = 'Classer: Verify your account';
        Mail::to($email)->send(
            new SuperSimpleEmail($email, $subject, array(
                "title" => "Hi " . $user->name,
                "name" => $user->name,
                "button-label" => "Verify account",
                "button-link" => url('auth/register/verify/' . $user->email_verification_token),
                "content" => "Thank you for signing up. Please verify your email address by following the link below. If you have any questions or need help, contact us at info@classermedia.com."
            ))
        );
    }

    /**
     * Account verified email.
     */
    static public function accountVerified($email, $user)
    {
        $subject = 'Classer: Welcome Aboard!';
        Mail::to($email)->send(
            new TemplateOne($email, $subject, array(
                "name" => $user->name,
            ))
        );
    }

    /**
     * Password reset email.
     */
    static public function passwordReset($email, $user)
    {
        $subject = 'Classer: Reset your password';
        Mail::to($email)->send(
            new SuperSimpleEmail($email, $subject, array(
                "title" => "Hi " . $user->name,
                "name" => $user->name,
                "button-label" => "Download Classer",
                "button-link" => url('auth/password/reset' . $user->email_verification_token),
                "content" => "You account has been successfully verified. If you have the Classer app installed, you can now log in and start using it. If you don't have the app installed, you can download it by clicking the Download button. If you have any questions or need help, please contact us at info@classermedia.com."
            ))
        );
    }
}
