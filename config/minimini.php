<?php

declare(strict_types=1);

return [

    'defaults' => [
        'unit_price_usd' => '0.01',
        'referral_bonus_units' => 2,
        'min_payment_usd' => '10.00',
        'site_name' => 'minimini.org',
        'contact_email' => 'hello@minimini.org',
        'social_links' => [
            'facebook' => '',
            'x' => '',
            'instagram' => '',
            'linkedin' => '',
            'youtube' => '',
        ],
    ],

    'super_admin' => [
        'name' => env('SUPER_ADMIN_NAME', 'Super Admin'),
        'email' => env('SUPER_ADMIN_EMAIL'),
        'password' => env('SUPER_ADMIN_PASSWORD'),
        'phone' => env('SUPER_ADMIN_PHONE'),
        'country' => env('SUPER_ADMIN_COUNTRY', 'United States'),
    ],

    'seed_demo_data' => (bool) env('SEED_DEMO_DATA', false),

    'trusted_proxies' => env('TRUSTED_PROXIES'),

];
