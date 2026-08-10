<?php

use App\Enums\AccountStatus;
use App\Jobs\MailAbandonedCart;
use App\Jobs\MailFeatureAnnouncement;
use App\Jobs\MailEarlyAccessInvite;
use App\Jobs\MailInactiveUserReminder;
use App\Jobs\MailMaintenanceNotice;
use App\Jobs\MailProductUpdate;
use App\Jobs\MailServiceAnnouncement;
use App\Jobs\MailUserAccountVerify;
use App\Jobs\MailUserFeedbackRequest;
use App\Jobs\MailUserGettingStarted;
use App\Jobs\MailUserReviewReminder;
use App\Jobs\MailUserWelcome;

$scheduleQueueWorkers = (bool) env('SCHEDULE_QUEUE_WORKERS', true);

return [
    /**
     * Admin email addresses for notifications
     */
    'admin_email' => env('APP_ADMIN_EMAILS', 'admin@example.com,support@example.com'),

    /**
     * Restrict checkout routes behind a temporary access key for live QA.
     */
    'checkout_access' => [
        'enabled' => (bool) env('CHECKOUT_ACCESS_ENABLED', false),
        'key' => env('CHECKOUT_ACCESS_KEY', ''),
        'query_param' => env('CHECKOUT_ACCESS_QUERY_PARAM', 'access'),
    ],

    /**
     * How long (in minutes) post metadata fetched from S3 is cached.
     * Set to 0 in .env to disable caching.
     */
    'posts_metadata_cache_ttl_minutes' => (int) env('POSTS_METADATA_CACHE_TTL_MINUTES', 60),

    /**
     * Scheduler configuration
     */
    'scheduler' => [
        ...($scheduleQueueWorkers ? [
            'mail' => [
                // Process all pending mail jobs then exit; retry failures; short sleep between polls
                'artisan' => [
                    'command' => 'queue:work',
                    'parameters' => [
                        'connection' => 'database',
                        '--queue' => 'mail',
                        '--stop-when-empty' => true,
                        '--sleep' => 1,
                        '--tries' => 3,
                        '--timeout' => 120,
                    ],
                ],
                'command' => 'queue:work database --queue=mail --stop-when-empty --sleep=1 --tries=3 --timeout=120',
                'expression' => env('CRON_EXPRESSION_MAIL', '* * * * *'), // Every minute
                'withoutOverlapping' => 5, // prevents a new run if previous <5 min old
                'background' => false,
                'output' => env('SCHEDULER_MAIL_OUTPUT', 'queue-mail.log'),
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
            'category' => 'Marketing',
            'job' => MailEarlyAccessInvite::class,
            'account_statuses' => [AccountStatus::VERIFIED->value],
        ],
        'review_reminder' => [
            'label' => 'Review Reminder',
            'description' => 'Ask verified users to leave product feedback.',
            'category' => 'Marketing',
            'job' => MailUserReviewReminder::class,
            'account_statuses' => [AccountStatus::VERIFIED->value],
        ],
        'account_verification' => [
            'label' => 'Account Verification',
            'description' => 'Send account verification links to inactive users.',
            'category' => 'Transactional',
            'job' => MailUserAccountVerify::class,
            'account_statuses' => [AccountStatus::INACTIVE->value],
        ],
        'welcome' => [
            'label' => 'Welcome Email',
            'description' => 'Welcome newly verified users and introduce key features.',
            'category' => 'Onboarding',
            'job' => MailUserWelcome::class,
            'account_statuses' => [AccountStatus::VERIFIED->value],
        ],
        'getting_started' => [
            'label' => 'Getting Started',
            'description' => 'Help verified users complete their initial setup.',
            'category' => 'Onboarding',
            'job' => MailUserGettingStarted::class,
            'account_statuses' => [AccountStatus::VERIFIED->value],
        ],
        'feature_announcement' => [
            'label' => 'Feature Announcement',
            'description' => 'Notify active users about a new product feature.',
            'category' => 'Announcements',
            'job' => MailFeatureAnnouncement::class,
            'account_statuses' => [AccountStatus::VERIFIED->value],
        ],
        'product_update' => [
            'label' => 'Product Update',
            'description' => 'Send general product updates and improvements.',
            'category' => 'Announcements',
            'job' => MailProductUpdate::class,
            'account_statuses' => [AccountStatus::VERIFIED->value],
        ],
        'inactive_user_reminder' => [
            'label' => 'Inactive User Reminder',
            'description' => 'Encourage inactive users to return and complete their account setup.',
            'category' => 'Onboarding',
            'job' => MailInactiveUserReminder::class,
            'account_statuses' => [AccountStatus::INACTIVE->value],
        ],
        'feedback_request' => [
            'label' => 'Feedback Request',
            'description' => 'Request feedback about the product or a recently released feature.',
            'category' => 'Marketing',
            'job' => MailUserFeedbackRequest::class,
            'account_statuses' => [AccountStatus::VERIFIED->value],
        ],
        'service_announcement' => [
            'label' => 'Service Announcement',
            'description' => 'Send important service or platform announcements.',
            'category' => 'Announcements',
            'job' => MailServiceAnnouncement::class,
            'account_statuses' => [
                AccountStatus::VERIFIED->value,
                AccountStatus::INACTIVE->value,
            ],
        ],
        'maintenance_notice' => [
            'label' => 'Maintenance Notice',
            'description' => 'Notify users about planned maintenance or temporary downtime.',
            'category' => 'Announcements',
            'job' => MailMaintenanceNotice::class,
            'account_statuses' => [AccountStatus::VERIFIED->value],
        ],
        'abandoned_cart' => [
            'label' => 'Abandoned Cart',
            'description' => 'Remind users to return and complete checkout after leaving items in cart.',
            'category' => 'Marketing',
            'job' => MailAbandonedCart::class,
            'account_statuses' => [AccountStatus::VERIFIED->value],
        ],
    ],
];
