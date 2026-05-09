<?php

return [
    /**
     * Deployment-Art: `cloud` = gekapselte Tenant-Instanz auf gemeinsamem Host (Subdomain),
     * dann zeigt der Admin u. a. „Betriebs-Versand“ ohne sichtbare SMTP-Zugangsdaten.
     * Standard `self_hosted` — Umgebungsvariable: `CLH_DEPLOYMENT`.
     */
    'deployment' => env('CLH_DEPLOYMENT', 'self_hosted'),

    /**
     * Absoluter Pfad zum Projektroot (ohne trailing slash). Umgebungsvariable: `CLH_APP_ROOT`.
     * Wird von `scripts/install-server.sh` in `.env` gesetzt. Leer/null = keine Pfadbindung (typisch lokal).
     * Shell: `scripts/update-application.sh` liest dieselbe Variable direkt aus `.env` und bricht bei Abweichung ab.
     */
    'app_root' => filled(env('CLH_APP_ROOT')) ? rtrim((string) env('CLH_APP_ROOT'), '/\\') : null,

    /**
     * Öffentliches Release-Manifest (nur JSON, wie Rechnungsschmiede-update).
     * Standard: pCloud Public Folder → filedn.eu … /creator-link-hub/update/versions.json
     */
    'update_manifest_url' => env(
        'CLH_UPDATE_MANIFEST_URL',
        'https://filedn.eu/lFa08iL0cJzHeyFFtNiVfqY/creator-link-hub/update/versions.json',
    ),

    /**
     * In dieser Installation ausgelieferte Release-Version (Semver). Bei jedem Release hochsetzen
     * und mit Eintrag in distribution/creator-link-hub-update/versions.json abgleichen.
     */
    'installed_version' => env('CLH_INSTALLED_VERSION', '0.1.0'),

    /**
     * Filament-Admin: verfügbare Oberflächensprachen (Vendor-Übersetzungen in vendor/filament/.../lang).
     */
    'filament_locales' => [
        'en' => ['native' => 'English', 'flag' => '🇬🇧'],
        'de' => ['native' => 'Deutsch', 'flag' => '🇩🇪'],
        'fr' => ['native' => 'Français', 'flag' => '🇫🇷'],
        'it' => ['native' => 'Italiano', 'flag' => '🇮🇹'],
    ],

    'free_link_limit' => 10,

    'stripe_prices' => [
        'free' => env('STRIPE_PRICE_FREE'),
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
