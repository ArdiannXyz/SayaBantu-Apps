<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Web Push / VAPID Configuration
    |--------------------------------------------------------------------------
    |
    | VAPID keys for browser web push notifications (Chrome, Firefox, Safari/iOS, Edge).
    | Generated according to RFC 8292 standard.
    |
    */

    'vapid' => [
        'subject' => env('VAPID_SUBJECT', 'mailto:admin@sayabantu.com'),
        'public_key' => env('VAPID_PUBLIC_KEY', 'BLk4HBzS21wQwaULvNZHoUjhCsAittZ8TolVKvVbESssAHLEWgTVFeWxm7XAkA1yT-pTLL9VwsrjynncPyV7s_k'),
        'private_key' => env('VAPID_PRIVATE_KEY', 'daSlXCR0K5QYhbgurs3YE8p5zHDxvReP1Q5YAMdUf0s'),
    ],

    /*
    | Automatic OpenSSL configuration on Windows if needed
    */
    'openssl_conf' => env('OPENSSL_CONF', 'C:/laragon/bin/php/php-8.3/extras/ssl/openssl.cnf'),
];
