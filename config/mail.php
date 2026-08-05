<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Mailer
    |--------------------------------------------------------------------------
    |
    | This option controls the default mailer that is used to send all email
    | messages unless another mailer is explicitly specified when sending
    | the message. All additional mailers can be configured within the
    | "mailers" array. Examples of each type of mailer are provided.
    |
    */

    'default' => env('MAIL_MAILER', 'log'),

    /*
    |--------------------------------------------------------------------------
    | Mailer Configurations
    |--------------------------------------------------------------------------
    |
    | "smtp" (default) → noreply mailbox — auth, signup, forgot/reset password.
    | "support" → support mailbox — used to send as info@ / admin@ aliases
    |             and other non-auth flows (invoices, shop status, etc.).
    |
    */

    'mailers' => [

        'smtp' => [
            'transport' => 'smtp',
            'scheme' => env('MAIL_SCHEME'),
            'url' => env('MAIL_URL'),
            'host' => env('MAIL_HOST', '127.0.0.1'),
            'port' => env('MAIL_PORT', 2525),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'timeout' => null,
            'local_domain' => env('MAIL_EHLO_DOMAIN', parse_url((string) env('APP_URL', 'http://localhost'), PHP_URL_HOST)),
        ],

        'support' => [
            'transport' => 'smtp',
            'scheme' => env('MAIL_SUPPORT_SCHEME', env('MAIL_SCHEME')),
            'host' => env('MAIL_SUPPORT_HOST', env('MAIL_HOST', '127.0.0.1')),
            'port' => env('MAIL_SUPPORT_PORT', env('MAIL_PORT', 2525)),
            'username' => env('MAIL_SUPPORT_USERNAME', env('MAIL_USERNAME')),
            'password' => env('MAIL_SUPPORT_PASSWORD', env('MAIL_PASSWORD')),
            'timeout' => null,
            'local_domain' => env('MAIL_EHLO_DOMAIN', parse_url((string) env('APP_URL', 'http://localhost'), PHP_URL_HOST)),
        ],

        'ses' => [
            'transport' => 'ses',
        ],

        'postmark' => [
            'transport' => 'postmark',
        ],

        'resend' => [
            'transport' => 'resend',
        ],

        'sendmail' => [
            'transport' => 'sendmail',
            'path' => env('MAIL_SENDMAIL_PATH', '/usr/sbin/sendmail -bs -i'),
        ],

        'log' => [
            'transport' => 'log',
            'channel' => env('MAIL_LOG_CHANNEL'),
        ],

        'array' => [
            'transport' => 'array',
        ],

        'failover' => [
            'transport' => 'failover',
            'mailers' => [
                'smtp',
                'log',
            ],
            'retry_after' => 60,
        ],

        'roundrobin' => [
            'transport' => 'roundrobin',
            'mailers' => [
                'ses',
                'postmark',
            ],
            'retry_after' => 60,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Global "From" Address (auth / default = noreply)
    |--------------------------------------------------------------------------
    */

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'noreply@obtainsolutions.com'),
        'name' => env('MAIL_FROM_NAME', env('APP_NAME', 'Laravel')),
    ],

    /*
    |--------------------------------------------------------------------------
    | Named From Addresses
    |--------------------------------------------------------------------------
    |
    | noreply  → real mailbox (auth mailer "smtp")
    | support  → real mailbox (mailer "support")
    | info     → alias of support (send via mailer "support")
    | admin    → alias of support (send via mailer "support")
    |
    */

    'addresses' => [
        'noreply' => env('MAIL_FROM_ADDRESS', 'noreply@obtainsolutions.com'),
        'support' => env('MAIL_SUPPORT_ADDRESS', 'support@obtainsolutions.com'),
        'info' => env('MAIL_INFO_ADDRESS', 'info@obtainsolutions.com'),
        'admin' => env('MAIL_ADMIN_ADDRESS', 'admin@obtainsolutions.com'),
    ],

];
