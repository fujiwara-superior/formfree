<?php

return [
    'key'             => env('STRIPE_KEY'),
    'secret'          => env('STRIPE_SECRET'),
    'webhook_secret'  => env('STRIPE_WEBHOOK_SECRET'),
    'prices' => [
        'standard' => env('STRIPE_STANDARD_PRICE_ID'),
        'pro'      => env('STRIPE_PRO_PRICE_ID'),
    ],
];
