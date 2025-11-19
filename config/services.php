<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'shelly' => [
        'gateway_url' => env('SHELLY_GATEWAY_URL'),
        'auth_token' => env('SHELLY_AUTH_TOKEN'),
        'timeout' => 10,
        'retries' => 3,
    ],
    'reservations' => [
        // Default price (in CZK) used to estimate revenue when there's no payments table.
        // You can override with RESERVATION_PRICE in .env
        'default_price' => env('RESERVATION_PRICE', 0),
    ],
];
