<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Mail;
use App\Mail\TemplateOne;
use App\Mail\SuperSimpleEmail;
use App\Mail\AdminAnalyticsReport;
use App\Models\Subscription;
use App\Models\User;

class MailSenderController extends Controller
{
    /**
     * Admin analytics report.
     */
    public static function sendAdminAnalyticsReport($data)
    {
        Mail::to('info@classermedia.com')->send(new AdminAnalyticsReport($data));
    }

    /**
     * Send Admin error alert.
     */
    public static function sendAdminErrorAlert(string $message, array $error = [])
    {
        $subject = 'Classer Error Alert';
        Mail::to('info@classermedia.com')->send(
            new SuperSimpleEmail('info@classermedia.com', $subject, [
                'title' => $message,
                'name' => 'Classer Admin',
                'button-label' => 'View Logs',
                'button-link' => url('auth/admin/login'),
                'content' => collect($error)
                    ->filter(fn($value) => filled($value)) // skip empty/null entries
                    ->map(function ($value, $key) {
                        return sprintf(
                            '<strong>%s:</strong> %s<br/><br/>',
                            e($key),
                            e((string) $value)
                        );
                    })
                    ->implode(''),
            ])
        );
    }

    /**
     * Verify account email.
     */
    public static function verifyAccount(User $user)
    {
        $subject = 'Verify your account';
        Mail::to($user->email)->send(
            new SuperSimpleEmail($user->email, $subject, [
                'title' => 'Hi ' . $user->name,
                'name' => $user->name,
                'button-label' => 'Verify account',
                // classer::/auth/register/verify/
                'button-link' => url('auth/register/verify/' . $user->email_verification_token),
                'content' => 'Thank you for signing up. Please verify your email address by following the link below. If you have any questions or need help, contact us at contact@classermedia.com.',
            ]),
        );
    }

    /**
     * Account verified email.
     */
    public static function accountVerified(User $user)
    {
        $subject = 'Welcome aboard!';
        Mail::to($user->email)->send(
            new TemplateOne($user->email, $subject, [
                'name' => $user->name,
            ]),
        );
    }

    /**
     * Password Reset Request email.
     */
    public static function passwordReset(User $user)
    {
        $subject = 'Password Reset Request';
        Mail::to($user->email)->send(
            new SuperSimpleEmail($user->email, $subject, [
                'title' => 'Hi ' . $user->name,
                'name' => $user->name,
                'button-label' => 'Reset password',
                'button-link' => url('auth/password/reset/' . $user->password_reset_token),
                'content' => 'We received a request to reset your password. If you did not make this request, please ignore this email. Otherwise, please click the button below to reset your password. If you have any questions or need help, contact us at contact@classermedia.com.',
            ]),
        );
    }

    /**
     * Password Reset email success.
     */
    public static function passwordResetSuccess(User $user)
    {
        $subject = 'Password Reset Successful';
        Mail::to($user->email)->send(
            new SuperSimpleEmail($user->email, $subject, [
                'title' => 'Hi ' . $user->name,
                'name' => $user->name,
                'button-label' => 'Visit Classer',
                'button-link' => url('/'),
                'content' => 'Your password has been reset. If you have any questions or need help, contact us at contact@classermedia.com.',
            ]),
        );
    }

    /**
     *  Login reminder email.
     */
    public static function loginReminder(User $user)
    {
        $subject = 'Login Reminder';
        Mail::to($user->email)->send(
            new SuperSimpleEmail($user->email, $subject, [
                'title' => 'Hi ' . $user->name,
                'name' => $user->name,
                'button-label' => 'Download Classer',
                'button-link' => url('https://classermedia.com/?modal=download'),
                'content' => "Hey 👋 We noticed that you have recently signed up to Classer but have not logged in yet. Have you been able to download the app from our website or the Microsoft Store? It's packed full of awesome features that will help you make the most of your recordings. Find out more over at <a href=\"classermedia.com\">classermedia.com</a>. If you have any questions or need help, we would love to hear form you. You can reach us at contact@classermedia.com."
            ]),
        );
    }

    /**
     * Verify account email.
     */
    public static function reviewReminder(User $user)
    {
        $subject = 'Enjoying Classer? We would love to hear your feedback';
        Mail::to($user->email)->send(
            new SuperSimpleEmail($user->email, $subject, [
                'title' => 'Hi ' . $user->name,
                'name' => $user->name,
                'button-label' => 'Give feedback',
                'button-link' => 'https://tally.so/r/nrPZR2',
                'content' => "Hi 👋, How's it going with Classer? We hope you are enjoying the app and all that it has to offer. We would love to hear your feedback on features you are enjoying and how we can help improve your experience. You can help us by completing the short form, it should only take a moment and we would love your input 😊.<br/> <br/> Thankyou for being part of the Classer community.",
            ]),
        );
    }

    /**
     * Subscription activated email.
     */
    public static function subscriptionActivated(User $user, Subscription $subscription)
    {
        // format the string to include subscription name if available
        $subject = sprintf(
            'Subscription Activated (%s)',
            $subscription && isset($subscription->title) ? $subscription->title : 'Premium'
        );

        Mail::to($user->email)->send(
            new SuperSimpleEmail($user->email, $subject, [
                'title' => 'Hi ' . $user->name,
                'name' => $user->name,
                'button-label' => 'More info',
                'button-link' => url('https://classermedia.com'),
                'content' => "Thank you for subscribing to Classer! Your subscription is now active and you can start enjoying all the premium features we have to offer. If you have any questions or need help, contact us at info@classermedia.com.",
            ]),
        );
    }
}
