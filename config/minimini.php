<?php

declare(strict_types=1);

return [

    'defaults' => [
        'unit_price_usd' => '0.01',
        'referral_bonus_units' => 2,
        'min_payment_usd' => '10.00',
        'site_name' => 'minimini.org',
        'contact_email' => 'hello@minimini.org',
        'whatsapp_number' => '',
        'office_address' => "12 Marina Road\nLagos Island\nLagos, Nigeria",
        'map_embed_url' => 'https://www.openstreetmap.org/export/embed.html?bbox=3.379%2C6.443%2C3.410%2C6.460&layer=mapnik&marker=6.4549%2C3.3947',
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
