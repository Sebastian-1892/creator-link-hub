<?php

return [
    /**
     * Absoluter Pfad zum Projektroot (ohne trailing slash). Umgebungsvariable: `CLH_APP_ROOT`.
     * Wird von `scripts/install-server.sh` in `.env` gesetzt. Leer/null = keine Pfadbindung (typisch lokal).
     * Shell: `scripts/update-from-git.sh` liest dieselbe Variable direkt aus `.env` und bricht bei Abweichung ab.
     */
    'app_root' => filled(env('CLH_APP_ROOT')) ? rtrim((string) env('CLH_APP_ROOT'), '/\\') : null,

    /**
     * Filament-Admin: verfügbare Oberflächensprachen (Vendor-Übersetzungen in vendor/filament/.../lang).
     */
    'filament_locales' => [
        'en' => ['native' => 'English'],
        'de' => ['native' => 'Deutsch'],
        'fr' => ['native' => 'Français'],
        'it' => ['native' => 'Italiano'],
    ],

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
