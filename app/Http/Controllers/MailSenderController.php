<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Mail;
use App\Mail\TemplateOne;
use App\Mail\TemplateTwo;
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
        $to = 'info@classermedia.com';
        $subject = 'Classer Error Alert';
        $content = collect($error)
            ->filter(fn($value) => filled($value)) // skip empty/null entries
            ->map(function ($value, $key) {
                return EmailHelper::render(
                    <<<HTML
                        <p><strong>{key}:</strong></p>
                        <pre>{value}</pre>
                    HTML
                    ,
                    [
                        'key' => e($key),
                        'value' => (string) e($value),
                    ],
                );
            })
            ->implode('');

        Mail::to('info@classermedia.com')->send(
            new SuperSimpleEmail($to, $subject, [
                'title' => $message,
                'name' => 'Classer Admin',
                'button-label' => 'View Logs',
                'button-link' => url('auth/admin/login'),
                'content' => $content,
            ]),
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
                <p>Please verify your email address by following the link below. If you have any questions or need help, contact us at <a href="mailto:{appContact}">{appContact}</a>.</p>
            HTML
            ,
            [
                'name' => $user->name,
                'appContact' => 'contact@classermedia.com',
            ],
        );

        Mail::to($to)->send(
            new SuperSimpleEmail($to, $subject, [
                'title' => 'Hi ' . $user->name,
                'name' => $user->name,
                'content' => $content,
                'button-label' => 'Verify account',
                // classer::/auth/register/verify/
                'button-link' => url('auth/register/verify/' . $user->email_verification_token),
            ]),
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
            new TemplateOne($to, $subject, [
                'name' => $user->name,
            ]),
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
                <p>We received a request to reset your password. If you did not make this request, please ignore this email. Otherwise, please click the button below to reset your password.</p>
                <p>If you have any questions or need help, contact us at <a href="mailto:{appContact}">{appContact}</a>.</p>
            HTML
            ,
            [
                'name' => $user->name,
                'appContact' => 'contact@classermedia.com',
            ],
        );

        Mail::to($to)->send(
            new SuperSimpleEmail($to, $subject, [
                'title' => 'Hi ' . $user->name,
                'name' => $user->name,
                'button-label' => 'Reset password',
                'button-link' => url(path: 'auth/password/reset/' . $user->password_reset_token),
                'content' => $content,
            ]),
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
                <p>Your password has been reset.</p>
                <p>If you have any questions or need help, contact us at <a href="mailto:{appContact}">{appContact}</a>.</p>
            HTML
            ,
            [
                'name' => $user->name,
                'appContact' => 'contact@classermedia.com',
            ],
        );

        Mail::to($to)->send(
            new SuperSimpleEmail($to, $subject, [
                'title' => 'Hi ' . $user->name,
                'name' => $user->name,
                'button-label' => 'Visit Classer',
                'button-link' => url('/'),
                'content' => $content,
            ]),
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
                <p>How's it going with Classer?</p>
                <p>We noticed that you have recently signed up to Classer but have not logged in yet. Have you been able to download the app from our <a href="{website}" >website</a> or the <a href="{msStore}">Microsoft Store?</a>. It's packed full of features that will help you make the most of your recordings. Find out more over at <a href=\"classermedia.com\">classermedia.com</a>.</p>
                <p>If you have any questions or need help, you can reach us at <a href="mailto:{appContact}">{appContact}</a>.</p>
            HTML
            ,
            [
                'name' => $user->name,
                'appContact' => 'contact@classermedia.com',
                'msStore' => 'https://apps.microsoft.com/detail/9mtw32cfv272?hl=en-US&gl=US',
                'website' => 'https://classermedia.com/',
            ],
        );

        Mail::to($to)->send(
            new SuperSimpleEmail($to, $subject, [
                'title' => 'Hi ' . $user->name,
                'name' => $user->name,
                'button-label' => 'Download Classer',
                'button-link' => url('/?modal=download'),
                'content' => $content,
            ]),
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
                <p>We hope you are enjoying the app and all that it has to offer. Your feedback helps us shape the future, tell us what’s working well and what we can improve to make your experience even smoother.</p>
                <p>You can help us by completing the short <a href="{surveyUrl}" >form</a>, it should only take a moment and we would love your input.</p>
            HTML
            ,
            [
                'name' => $user->name,
                'surveyUrl' => 'https://tally.so/r/nrPZR2',
            ],
        );

        Mail::to($to)->send(
            new SuperSimpleEmail($to, $subject, [
                'title' => 'Hi ' . $user->name,
                'name' => $user->name,
                'button-label' => 'Give feedback',
                'button-link' => 'https://tally.so/r/nrPZR2',
                'content' => $content,
            ]),
        );
    }

    /**
     * Subscription activated email.
     */
    public static function subscriptionActivated(User $user, Subscription $subscription)
    {
        $to = $user->email;
        $subject = sprintf('Welcome to Classer %s', $subscription->title);

        $content = EmailHelper::render(
            <<<HTML
                <p>Your account has been upgraded to the <strong>{title}</strong> plan, giving you access to all the key features to organise, save, and share your best moments.</p>
                <p>Your plan is active until <strong>{subExpr}</strong>, so you can explore everything Classer has to offer.</p>
                <p>If you have any questions or need help getting started, just reach out at <a href="mailto:{appContact}">{appContact}</a></p>
                <p>We’re also putting together a small Instagram chat for early testers, follow <a href="https://www.instagram.com/weareclassermedia/">@weareclassermedia</a> and drop us a quick DM if you’d like to join.</p>
            HTML
            ,
            [
                'name' => $user->name,
                'title' => $subscription->title,
                'subExpr' => $user->subscription->expiration_date->toFormattedDateString(),
                'appContact' => 'contact@classermedia.com',
            ],
        );

        Mail::to($to)->send(
            new SuperSimpleEmail($to, $subject, [
                'title' => 'Hi ' . $user->name,
                'name' => $user->name,
                'content' => $content,
                'button-link' => url('https://www.instagram.com/weareclassermedia/'),
                'button-label' => 'Join our Instagram',
            ]),
        );
    }

    /**
     * Subscription activated email.
     */
    public static function inviteUserToEarlyAccess(User $user)
    {
        $to = $user->email;
        $subject = sprintf('Early Access to Classer Essentials');
        $inviteLink = url('/insiders/classer-share?email=' . $to);
        $body = EmailHelper::render(
            <<<HTML
                <div class="fr-element fr-view" dir="auto" contenteditable="true" aria-disabled="false" spellcheck="true">
                    <p><span style="color: rgb(133, 133, 136);">We’re excited to invite you to try <strong>{title}</strong>, our upcoming subscription that makes it effortless to share your action cam moments, privately, securely, and without compression.</span></p>
                    <p><span style="color: rgb(133, 133, 136);">&nbsp;</span></p>
                    <p><span style="color: rgb(133, 133, 136);">We’re inviting a small group of users to <strong>try&nbsp;</strong><strong><span style="background-color: rgb(255, 255, 255);">{title} before launch for free</span></strong><strong><span style="background-color: rgb(255, 255, 255);">.</span></strong></span></p>
                </div>
            HTML
            ,
            [
                'title' => 'Classer Essentials',
            ],
        );

        $emailData = [
            'title' => 'Get early access to our new private cloud sharing feature',
            'heroImage' => @asset('/assets/email-images/insiders-invite-classer-share/hero.jpeg'),
            'body' => $body,
            'features' => [
                [
                    'image' => 'https://img.mailinblue.com/6077057/images/content_library/original/68f9f6fd77b682f85c5556b2.png',
                    'title' => 'Private sharing',
                    'description' => 'Share privately in full quality with links that expire after 24 hours.',
                ],
                [
                    'image' => 'https://img.mailinblue.com/6077057/images/content_library/original/68f9f6fdacdac9faba52af25.png',
                    'title' => 'Free Storage',
                    'description' => 'Get 100GB of cloud space to store your best moments.',
                ],
                [
                    'image' => 'https://img.mailinblue.com/6077057/images/content_library/original/68f9f6fdacdac9faba52af26.png',
                    'title' => 'Share feedback',
                    'description' => 'Help shape the future of Classer by sharing your feedback.',
                ],
            ],
            'titleTwo' => 'Ready to join us?',
            'taglineTwo' => 'Click below to open your invite page and accept your spot in Classer Essentials.',
            'buttonLabel' => 'Get Early Access',
            'buttonLink' => $inviteLink,
            'footerText' => 'This early test is limited to a small group of users. Please don’t share your link. Early testers will be asked for feedback to help us improve Classer. By joining, you accept our T&Cs.',
        ];

        Mail::to($to)->send(new TemplateTwo($to, $subject, $emailData));
    }
}
