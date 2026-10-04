<?php

declare(strict_types=1);

return [

    'public_key' => env('FLUTTERWAVE_PUBLIC_KEY'),

    'secret_key' => env('FLUTTERWAVE_SECRET_KEY'),

    'secret_hash' => env('FLUTTERWAVE_SECRET_HASH'),

    'base_url' => rtrim((string) env('FLUTTERWAVE_BASE_URL', 'https://api.flutterwave.com'), '/'),

];
