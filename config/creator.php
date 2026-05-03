<?php

return [
    'free_link_limit' => 10,

    'stripe_prices' => [
        'starter' => env('STRIPE_PRICE_STARTER'),
        'pro' => env('STRIPE_PRICE_PRO'),
    ],

    'reserved_slugs' => [
        'p', 'admin', 'api', 'app', 'billing', 'dashboard', 'filament', 'go',
        'livewire', 'login', 'logout', 'pricing', 'register', 'up',
        'verify-email', 'forgot-password', 'reset-password', 'confirm-password',
        'onboarding', 'links', 'analytics', 'settings', 'profile', 'team',
        'sitemap.xml', 'robots.txt', 'help',
    ],

    'plans' => [
        'free' => [
            'stripe_price' => env('STRIPE_PRICE_FREE'), // optional placeholder
            'link_limit' => 10,
            'platform_branding' => true,
        ],
        'starter' => [
            'stripe_price' => env('STRIPE_PRICE_STARTER'),
            'link_limit' => null,
            'platform_branding' => false,
        ],
        'pro' => [
            'stripe_price' => env('STRIPE_PRICE_PRO'),
            'link_limit' => null,
            'platform_branding' => false,
        ],
    ],
];
