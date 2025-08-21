<?php

return [
    /**
     * Admin email addresses for notifications
     */
    'admin_email' => env('APP_ADMIN_EMAILS', 'admin@example.com,support@example.com'),

    /**
     * Scheduler configuration
     */
    'scheduler' => [
        'mail' => [
            // Process all pending mail jobs then exit; retry failures; short sleep between polls
            'command' => 'queue:work --queue=mail --stop-when-empty --sleep=1 --tries=3 --timeout=120',
            'expression' => env('CRON_EXPRESSION_MAIL', '* * * * *'), // Every minute
        ],
        'cloudShareVerify' => [
            'command' => 'queue:work cloudshare --queue=verify --stop-when-empty --sleep=1 --tries=3 --timeout=300',
            'expression' => env('CRON_EXPRESSION_CLOUD_SHARE_VERIFY', '0 */4 * * *'), // Every 4 hours
        ],
        'cloudShareExpire' => [
            'command' => 'queue:work cloudshare --queue=expire --stop-when-empty --sleep=1 --tries=3 --timeout=600',
            'expression' => env('CRON_EXPRESSION_CLOUD_SHARE_EXPIRE', '0 0 * * *'), // Daily at midnight
        ],
    ],

    /**
     * Cloud Share configuration
     */
    'cloudShare' => [
        'directory' => env('CLOUD_SHARE_DIRECTORY', 'cloud-share'),
        'putObjectTimeout' => env('CLOUD_SHARE_S3_PUT_OBJECT_TIMEOUT', '+1 minute'),
        'getObjectTimeout' => env('CLOUD_SHARE_S3_GET_OBJECT_TIMEOUT', '+2 minutes'),
        'verifyDelay' => env('CLOUD_SHARE_VERIFY_DELAY', '+1 minute'),
        'expire_after' => env('CLOUD_SHARE_EXPIRE_AFTER', '+2 minutes'),
    ],
];