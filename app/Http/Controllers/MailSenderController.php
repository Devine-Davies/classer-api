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
        $subject = 'Verify your account';
        Mail::to($email)->send(
            new SuperSimpleEmail($email, $subject, array(
                "title" => "Hi " . $user->name,
                "name" => $user->name,
                "button-label" => "Verify In App",
                "button-link" => 'classer::/auth/register/verify/' . $user->email_verification_token,
                "website-button-link" => url('auth/register/verify/' . $user->email_verification_token),
                "content" => "Thank you for signing up. Please verify your email address by following the link below. If you have any questions or need help, contact us at info@classermedia.com."
            ))
        );
    }

    /**
     * Account verified email.
     */
    static public function accountVerified($email, $user)
    {
        $subject = 'Welcome aboard!';
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
        $subject = 'Reset your password';
        Mail::to($email)->send(
            new SuperSimpleEmail($email, $subject, array(
                "title"        => "Hi " . $user->name,
                "name"         => $user->name,
                "button-label" => "Reset password",
                "button-link"  => url('auth/password/reset/' . $user->password_reset_token),
                "content"      => "We received a request to reset your password. If you did not make this request, please ignore this email. Otherwise, please click the button below to reset your password. If you have any questions or need help, contact us at info@classermedia.com."
            ))
        );
    }

    /**
     * Password changed successfully email.
     */
    static public function passwordResetSuccess($email, $user)
    {
        $subject = 'Password changed successfully';
        Mail::to($email)->send(
            new SuperSimpleEmail($email, $subject, array(
                "title"        => "Hi " . $user->name,
                "name"         => $user->name,
                "button-label" => "Visit Classer",
                "button-link"  => url('/'),
                "content"      => "Your password has been changed successfully. If you have any questions or need help, contact us at info@classermedia.com."
            ))
        );
    }

    /**
     *  Login reminder email.
     */
    static public function loginReminder($email, $user)
    {
        $subject = 'Login Reminder';
        Mail::to($email)->send(
            new SuperSimpleEmail($email, $subject, array(
                "title"        => "Hi " . $user->name,
                "name"         => $user->name,
                "button-label" => "Download Classer",
                "button-link"  => url('https://classermedia.com/?modal=download'),
                "content"      => "Hey our records show that you haven't logged into Classer yet. Your missing out on some great features that will help you make the most of your recordings. Click the button below if you haven't already downloaded Classer. If you have any questions or need help, contact us at info@classermedia.com."
            ))
        );
    }
}
