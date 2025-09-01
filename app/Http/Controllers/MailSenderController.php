<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Mail;
use App\Mail\TemplateOne;
use App\Mail\SuperSimpleEmail;
use App\Mail\AdminAnalyticsReport;
use App\Models\Subscription;
use App\Models\User;
use App\Utils\EmailHelper;

/**
 * MailSenderController
 * Handles sending various types of emails to users and admins.
 */
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
        $to = "info@classermedia.com";
        $subject = 'Classer Error Alert';
        $content = collect($error)
            ->filter(fn($value) => filled($value)) // skip empty/null entries
            ->map(function ($value, $key) {
                return EmailHelper::render(
                    <<<HTML
                        <p><strong>{key}:</strong></p>
                        <pre>{value}</pre>
                    HTML,
                    [
                        'key' => e($key),
                        'value' => (string) e($value),
                    ]
                );
            })
            ->implode('');


        Mail::to('info@classermedia.com')->send(
            new SuperSimpleEmail(
                $to,
                $subject,
                [
                    'title' => $message,
                    'name' => 'Classer Admin',
                    'button-label' => 'View Logs',
                    'button-link' => url('auth/admin/login'),
                    'content' => $content,
                ]
            ),
        );
    }

    /**
     * Verify account email.
     */
    public static function verifyAccount(User $user)
    {
        $to = $user->email;
        $subject = 'Verify your account';
        $content = EmailHelper::render(
            <<<HTML
                <p>Hi <strong>{name}</strong> 👋, thank you for signing up.</p>
                <p>Please verify your email address by following the link below. If you have any questions or need help, contact us at <a href="mailto:{appContact}">{appContact}</a>.</p>
                <p>Thankyou for being part of the Classer community.</p>
            HTML,
            [
                'name' => $user->name,
                'appContact' => "contact@classermedia.com",
            ]
        );

        Mail::to($to)->send(
            new SuperSimpleEmail(
                $to,
                $subject,
                [
                    'title' => 'Hi ' . $user->name,
                    'name' => $user->name,
                    'content' => $content,
                    'button-label' => 'Verify account',
                    // classer::/auth/register/verify/
                    'button-link' => url('auth/register/verify/' . $user->email_verification_token),
                ]
            ),
        );
    }

    /**
     * Account verified email.
     */
    public static function accountVerified(User $user)
    {
        $to = $user->email;
        $subject = 'Welcome aboard!';
        Mail::to($to)->send(
            new TemplateOne(
                $to,
                $subject,
                [
                    'name' => $user->name,
                ]
            ),
        );
    }

    /**
     * Password Reset Request email.
     */
    public static function passwordReset(User $user)
    {
        $to = $user->email;
        $subject = 'Password Reset Request';
        $content = EmailHelper::render(
            <<<HTML
                <p>Hi <strong>{name}</strong> 👋, we received a request to reset your password.</p>
                <p>If you did not make this request, please ignore this email. Otherwise, please click the button below to reset your password.</p>
                <p>If you have any questions or need help, contact us at <a href="mailto:{appContact}">{appContact}</a>. Thankyou for being part of the Classer community.</p>
            HTML,
            [
                'name' => $user->name,
                'appContact' => "contact@classermedia.com",
            ]
        );

        Mail::to($to)->send(
            new SuperSimpleEmail(
                $to,
                $subject,
                [
                    'title' => 'Hi ' . $user->name,
                    'name' => $user->name,
                    'button-label' => 'Reset password',
                    'button-link' => url(path: 'auth/password/reset/' . $user->password_reset_token),
                    'content' => $content,
                ]
            ),
        );
    }

    /**
     * Password Reset email success.
     */
    public static function passwordResetSuccess(User $user)
    {
        $to = $user->email;
        $subject = 'Password Reset Successful';
        $content = EmailHelper::render(
            <<<HTML
                <p>Hi <strong>{name}</strong> 👋, Your password has been reset.</p>
                <p>If you have any questions or need help, contact us at <a href="mailto:{appContact}">{appContact}</a>.</p>
                <p>Thankyou for being part of the Classer community.</p>
            HTML,
            [
                'name' => $user->name,
                'appContact' => "contact@classermedia.com",
            ]
        );

        Mail::to($to)->send(
            new SuperSimpleEmail(
                $to,
                $subject,
                [
                    'title' => 'Hi ' . $user->name,
                    'name' => $user->name,
                    'button-label' => 'Visit Classer',
                    'button-link' => url('/'),
                    'content' => $content,
                ]
            ),
        );
    }

    /**
     *  Login reminder email.
     */
    public static function loginReminder(User $user)
    {
        $to = $user->email;
        $subject = 'Login Reminder';
        $content = EmailHelper::render(
            <<<HTML
                <p>Hi <strong>{name}</strong> 👋, How's it going with Classer?</p>
                <p>We noticed that you have recently signed up to Classer but have not logged in yet. Have you been able to download the app from our website or the Microsoft Store?</p>
                <p>It's packed full of awesome features that will help you make the most of your recordings. Find out more over at <a href=\"classermedia.com\">classermedia.com</a>.</p>
                <p>If you have any questions or need help, we would love to hear form you. You can reach us at contact@classermedia.com.</p>
            HTML,
            ['name' => $user->name]
        );

        Mail::to($to)->send(
            new SuperSimpleEmail(
                $to,
                $subject,
                [
                    'title' => 'Hi ' . $user->name,
                    'name' => $user->name,
                    'button-label' => 'Download Classer',
                    'button-link' => url('https://classermedia.com/?modal=download'),
                    'content' => $content,
                ]
            ),
        );
    }

    /**
     * Verify account email.
     */
    public static function reviewReminder(User $user)
    {
        $to = $user->email;
        $subject = 'Enjoying Classer? We would love to hear your feedback';
        $content = EmailHelper::render(
            <<<HTML
                <p>Hi <strong>{name}</strong> 👋, How's it going with Classer?</p>
                <p>We hope you are enjoying the app and all that it has to offer. We would love to hear your feedback on features you are enjoying and how we can help improve your experience.</p>
                <p>You can help us by completing the short form, it should only take a moment and we would love your input 😊. Thankyou for being part of the Classer community.</p>
            HTML,
            ['name' => $user->name]
        );

        Mail::to($to)->send(
            new SuperSimpleEmail(
                $to,
                $subject,
                [
                    'title' => 'Hi ' . $user->name,
                    'name' => $user->name,
                    'button-label' => 'Give feedback',
                    'button-link' => 'https://tally.so/r/nrPZR2',
                    'content' => $content,
                ]
            ),
        );
    }

    /**
     * Subscription activated email.
     */
    public static function subscriptionActivated(
        User $user,
        Subscription $subscription
    ) {
        $to = $user->email;
        $subject = sprintf(
            'Welcome to Classer %s',
            $subscription->title
        );

        $content = EmailHelper::render(
            <<<HTML
                <p>"Hi <strong>{name}</strong></p>
                <p>Your account has been upgraded to the <strong>{title}</strong> plan, giving you access to all the key features to organise, save, and share your best moments.</p>
                <p>Your plan is active until <strong>{subExpr}</strong>, so you can explore everything Classer has to offer.</p>
                <p>If you have any questions or need help getting started, just reach out at <a href="mailto:{appContact}">{appContact}</a></p>
            HTML,
            [
                'name' => $user->name,
                'title' => $subscription->title,
                'subExpr' => $user->subscription->expiration_date->toFormattedDateString(),
                'appContact' => "contact@classermedia.com",
            ]
        );

        Mail::to($to)->send(
            new SuperSimpleEmail(
                $to,
                $subject,
                [
                    'title' => 'Hi ' . $user->name,
                    'name' => $user->name,
                    'content' => $content,
                    'button-link' => url(sprintf('https://classermedia.com/subscriptions/%s', $subscription->title)),
                    'button-label' => 'Find out more',
                ]
            ),
        );
    }
}
