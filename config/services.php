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

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'flutterwave' => [
        'public_key' => env('FLW_PUBLIC_KEY', env('FLUTTERWAVE_PUBLIC_KEY')),
        'secret_key' => env('FLW_SECRET_KEY', env('FLUTTERWAVE_SECRET_KEY')),
        'encryption_key' => env('FLW_ENCRYPTION_KEY', env('FLUTTERWAVE_ENCRYPTION_KEY')),
        'webhook_hash' => env('FLW_WEBHOOK_HASH', env('FLUTTERWAVE_SECRET_HASH')),
        'redirect_url' => env('FLW_REDIRECT_URL'),
        'base_url' => rtrim((string) env('FLW_BASE_URL', env('FLUTTERWAVE_BASE_URL', 'https://api.flutterwave.com')), '/'),
        'currencies' => [
            'USD' => ['label' => 'US Dollar', 'options' => 'card'],
            'NGN' => ['label' => 'Nigerian Naira', 'options' => 'card,banktransfer,ussd'],
            'GHS' => ['label' => 'Ghanaian Cedi', 'options' => 'card,mobilemoneyghana'],
            'KES' => ['label' => 'Kenyan Shilling', 'options' => 'card,mpesa'],
            'TZS' => ['label' => 'Tanzanian Shilling', 'options' => 'card,mobilemoney'],
            'UGX' => ['label' => 'Ugandan Shilling', 'options' => 'card,mobilemoneyuganda'],
            'RWF' => ['label' => 'Rwandan Franc', 'options' => 'card,mobilemoneyrwanda'],
            'ZMW' => ['label' => 'Zambian Kwacha', 'options' => 'card,mobilemoneyzambia'],
            'XAF' => ['label' => 'Central African CFA', 'options' => 'card,mobilemoneyfranco'],
            'XOF' => ['label' => 'West African CFA', 'options' => 'card,mobilemoneyfranco'],
            'ZAR' => ['label' => 'South African Rand', 'options' => 'card'],
        ],
    ],

];
