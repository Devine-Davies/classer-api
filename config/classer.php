<?php

use App\Enums\AccountStatus;
use App\Jobs\MailEarlyAccessInvite;
use App\Jobs\MailUserAccountVerify;
use App\Jobs\MailUserReviewReminder;

$scheduleQueueWorkers = (bool) env('SCHEDULE_QUEUE_WORKERS', true);

return [
    /**
     * Admin email addresses for notifications
     */
    'admin_email' => env('APP_ADMIN_EMAILS', 'admin@example.com,support@example.com'),

    /**
     * Scheduler configuration
     */
    'scheduler' => [
        ...($scheduleQueueWorkers ? [
            'mail' => [
                // Process all pending mail jobs then exit; retry failures; short sleep between polls
                'command' => 'queue:work --queue=mail --stop-when-empty --sleep=1 --tries=3 --timeout=120',
                'expression' => env('CRON_EXPRESSION_MAIL', '* * * * *'), // Every minute
                'withoutOverlapping' => 5, // prevents a new run if previous <5 min old
            ],
            'cloudShareVerify' => [
                'command' => 'queue:work cloudshare --queue=verify --stop-when-empty --sleep=1 --tries=3 --timeout=300',
                'expression' => env('CRON_EXPRESSION_CLOUD_SHARE_VERIFY', '0 */4 * * *'), // Every 4 hours
                'withoutOverlapping' => 30, // prevents a new run if previous <30 min old
            ],
            'cloudShareExpire' => [
                'command' => 'queue:work cloudshare --queue=expire --stop-when-empty --sleep=1 --tries=3 --timeout=600',
                'expression' => env('CRON_EXPRESSION_CLOUD_SHARE_EXPIRE', '0 0 * * *'), // Daily at midnight
                'withoutOverlapping' => 60, // prevents a new run if previous <60 min old
            ],
        ] : []),
    ],

    /**
     * Cloud Share configuration
     */
    'cloudShare' => [
        'directory' => env('CLOUD_SHARE_DIRECTORY', 'cloud-share'),
        'putObjectTimeout' => env('CLOUD_SHARE_S3_PUT_OBJECT_TIMEOUT', '+1 minute'),
        'getObjectTimeout' => env('CLOUD_SHARE_S3_GET_OBJECT_TIMEOUT', '+2 minutes'),
        'verifyDelay' => env('CLOUD_SHARE_VERIFY_DELAY', '+1 minute'),
        'expireAfter' => env('CLOUD_SHARE_EXPIRE_AFTER', '+2 minutes'),
    ],

    /**
     * Templates available in the admin bulk email tool.
     */
    'admin_bulk_mail_templates' => [
        'early_access_invite' => [
            'label' => 'Early Access Invite',
            'description' => 'Invite verified users to Classer Essentials early access.',
            'job' => MailEarlyAccessInvite::class,
            'account_statuses' => [AccountStatus::VERIFIED->value],
        ],
        'review_reminder' => [
            'label' => 'Review Reminder',
            'description' => 'Ask verified users to leave product feedback.',
            'job' => MailUserReviewReminder::class,
            'account_statuses' => [AccountStatus::VERIFIED->value],
        ],
        'account_verification' => [
            'label' => 'Account Verification',
            'description' => 'Send account verification links to inactive users.',
            'job' => MailUserAccountVerify::class,
            'account_statuses' => [AccountStatus::INACTIVE->value],
        ],
    ],
];
