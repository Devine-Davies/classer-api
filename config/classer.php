<?php

return [
    // Application name
    'admin_email' => env('APP_ADMIN_EMAILS', 'admin@example.com,support@example.com'),

    // Cloud Share configuration
    'cloudShare' => [
        'directory' => env('CLOUD_SHARE_DIRECTORY', 'cloud-share'),
        'put_object_timeout' => env('CLOUD_SHARE_S3_PUT_OBJECT_TIMEOUT', '+1 minute'),
        'get_object_timeout' => env('CLOUD_SHARE_S3_GET_OBJECT_TIMEOUT', '+2 minutes'),
        'verify_delay' => env('CLOUD_SHARE_VERIFY_DELAY', '+1 minute'),
        'expire_after' => env('CLOUD_SHARE_EXPIRE_AFTER', '+2 minutes'),
    ],
];
