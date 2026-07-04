<?php

namespace App\Services;

use App\Mail\AdminAnalyticsReport;
use App\Mail\SimpleEmail;
use App\Mail\SuperSimpleEmail;
use App\Mail\TemplateOne;
use App\Mail\TemplateTwo;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\PromotionRedemption;
use App\Models\User;
use App\Models\UserSubscription;
use App\Utils\EmailHelper;
use Illuminate\Support\Facades\Mail;

/**
 * MailSenderService
 * Handles sending various types of emails to users and admins.
 */
class MailSenderService
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
            ->filter(fn ($value) => filled($value)) // skip empty/null entries
            ->map(function ($value, $key) {
                return EmailHelper::render(
                    <<<'HTML'
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
                'button-link' => url('admin/login'),
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
            <<<'HTML'
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
                'title' => 'Hi '.$user->name,
                'name' => $user->name,
                'content' => $content,
                'button-label' => 'Verify account',
                // classer::/auth/register/verify/
                'button-link' => url('auth/register/verify/'.$user->email_verification_token),
            ]),
        );
    }

    /**
     * Account verified email.
     *
     * @param  User  $user  The user whose account has been verified.
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
     *
     * @param  User  $user  The user requesting a password reset.
     */
    public static function passwordReset(User $user)
    {
        $to = $user->email;
        $subject = 'Password Reset Request';
        $content = EmailHelper::render(
            <<<'HTML'
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
                'title' => 'Hi '.$user->name,
                'name' => $user->name,
                'button-label' => 'Reset password',
                'button-link' => url(path: 'auth/password/reset/'.$user->password_reset_token),
                'content' => $content,
            ]),
        );
    }

    /**
     * Password Reset email success.
     *
     * @param  User  $user  The user whose password has been reset.
     */
    public static function passwordResetSuccess(User $user)
    {
        $to = $user->email;
        $subject = 'Password Reset Successful';
        $content = EmailHelper::render(
            <<<'HTML'
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
                'title' => 'Hi '.$user->name,
                'name' => $user->name,
                'button-label' => 'Visit Classer',
                'button-link' => url('/'),
                'content' => $content,
            ]),
        );
    }

    /**
     *  Login reminder email.
     *
     * @param  User  $user  The user to send the login reminder to.
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
                'title' => 'Hi '.$user->name,
                'name' => $user->name,
                'button-label' => 'Download Classer',
                'button-link' => url('/?modal=download'),
                'content' => $content,
            ]),
        );
    }

    /**
     * Verify account email.
     *
     * @param  User  $user  The user to send the review reminder to.
     */
    public static function reviewReminder(User $user)
    {
        $to = $user->email;
        $subject = 'Enjoying Classer? We would love to hear your feedback';
        $content = EmailHelper::render(
            <<<'HTML'
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
                'title' => 'Hi '.$user->name,
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
    public static function subscriptionActivated(User $user, UserSubscription $subscription): void
    {
        $to = $user->email;
        $subject = sprintf('Subscription Activated: %s', $subscription->plan->title);
        $content = EmailHelper::render(
            <<<'HTML'
                <p>Your account has been upgraded to the <strong>{title}</strong> plan, giving you access to all the key features to organise, save, and share your best moments.</p>
                <p>Your plan is active until <strong>{subExpr}</strong>, so you can explore everything Classer has to offer.</p>
                <p>If you have any questions or need help getting started, just reach out at <a href="mailto:{appContact}">{appContact}</a></p>
                <p>We’re also putting together a small Instagram chat for early testers, follow <a href="https://www.instagram.com/weareclassermedia/">@weareclassermedia</a> and drop us a quick DM if you’d like to join.</p>
            HTML
            ,
            [
                'name' => $user->name,
                'title' => $subscription->plan->title,
                'subExpr' => $user->subscription->expiration_date->toFormattedDateString(),
                'appContact' => 'contact@classermedia.com',
            ],
        );

        Mail::to($to)->send(
            new SuperSimpleEmail($to, $subject, [
                'title' => 'Hi '.$user->name,
                'name' => $user->name,
                'content' => $content,
                'button-link' => url('https://www.instagram.com/weareclassermedia/'),
                'button-label' => 'Join our Instagram',
            ]),
        );
    }

    /**
     * Subscription activated email.
     *
     * @param  User  $user  The user to invite to early access.
     */
    public static function inviteUserToEarlyAccess(User $user)
    {
        $to = $user->email;
        $subject = sprintf('Action cam users: Your free 100GB for sharing your clips is about to expire');
        $inviteLink = url('/insiders/classer-share?email='.$to);
        $body = EmailHelper::render(
            <<<'HTML'
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

    /**
     * Order payment confirmation email.
     *
     * @param  Order  $order  The order that has been paid for.
     * @param  OrderPayment  $payment  The payment that has been confirmed.
     */
    public static function orderPaymentConfirmed(Order $order, OrderPayment $payment): void
    {
        $to = $order->customer_email;
        if (! $to) {
            return;
        }

        $order->loadMissing('items');

        $name = $order->customer_name ?: 'there';
        $amount = number_format($order->amount / 100, 2);

        $productName = $order->items->isNotEmpty()
            ? $order->items->pluck('name_snapshot')->filter()->unique()->implode(', ')
            : ($order->product?->name ?: 'Classer product');

        $subject = 'Your Classer order is confirmed';
        $content = EmailHelper::render(
            <<<'HTML'
                <p>Thanks for your order. Your payment has been confirmed.</p>
                <p><strong>Order UID:</strong> {orderUid}</p>
                <p><strong>Products:</strong> {product}</p>
                <p><strong>Amount Paid:</strong> {currency} {amount}</p>
                <p><strong>Shipping:</strong> {line1}, {city}, {postalCode}, {country}</p>
                <p>If anything looks wrong, reply to this email and our team will help.</p>
            HTML,
            [
                'orderUid' => $order->uid,
                'product' => $productName,
                'currency' => strtoupper($order->currency),
                'amount' => $amount,
                'line1' => $order->shipping_line_1,
                'city' => $order->shipping_city,
                'postalCode' => $order->shipping_postal_code,
                'country' => strtoupper((string) $order->shipping_country),
            ]
        );

        Mail::to($to)->send(
            new SuperSimpleEmail($to, $subject, [
                'title' => 'Hi '.$name,
                'name' => $name,
                'button-label' => 'View your order',
                'button-link' => url('/checkout/'.$order->uid.'/success'),
                'content' => $content,
            ])
        );
    }

    /**
     * Promotional redeem email.
     *
     * @param  PromotionRedemption  $redemption  Redemption record.
     * @param  string  $token  Raw redeem token.
     */
    public static function promotionalRedeemEmail(PromotionRedemption $redemption, string $token): void
    {
        $redemption->loadMissing('order');

        $to = $redemption->customer_email;
        if (! $to) {
            return;
        }

        $name = $redemption->order?->customer_name ?: 'there';
        $redeemLink = url('/promotions/redeem?'.http_build_query([
            'email' => $to,
            'redeem_code' => $token,
        ]));

        $subject = 'Redeem your Classer promotion';
        $content = EmailHelper::render(
            <<<'HTML'
                <p>Your order qualifies for a promotion.</p>
                <p>Use your redeem code below on the redeem page to claim it:</p>
                <p><strong>{redeemCode}</strong></p>
                <p>If the code is not pre-filled, copy and paste it on the redeem form.</p>
            HTML,
            [
                'redeemCode' => $token,
            ]
        );

        Mail::to($to)->send(
            new SimpleEmail($to, $subject, [
                'title' => 'Hi '.$name,
                'button-label' => 'Redeem promotion',
                'button-link' => $redeemLink,
                'content' => $content,
            ])
        );

        app(PromotionRedemptionService::class)->markEmailed($redemption->fresh());
    }
}
