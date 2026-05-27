<?php

return [
    /**
     * Absoluter Pfad zum Projektroot (ohne trailing slash). Umgebungsvariable: `CLH_APP_ROOT`.
     * Wird von `scripts/clh-provision-tenant.sh` in der Tenant-`.env` gesetzt. Leer/null = lokal.
     * Shell: `scripts/update-application.sh` liest dieselbe Variable direkt aus `.env` und bricht bei Abweichung ab.
     */
    'app_root' => filled(env('CLH_APP_ROOT')) ? rtrim((string) env('CLH_APP_ROOT'), '/\\') : null,

    /**
     * Filament-Admin: verfügbare Oberflächensprachen (Vendor-Übersetzungen in vendor/filament/.../lang).
     */
    'filament_locales' => [
        'en' => ['native' => 'English', 'flag' => '🇬🇧'],
        'de' => ['native' => 'Deutsch', 'flag' => '🇩🇪'],
        'fr' => ['native' => 'Français', 'flag' => '🇫🇷'],
        'it' => ['native' => 'Italiano', 'flag' => '🇮🇹'],
    ],

    /**
     * Tenant-Standard-Sprache (Marketing-Fallback, Übersetzungs-Kette).
     */
    'tenant_default_locale' => env('TENANT_DEFAULT_LOCALE', 'de'),

  /**
   * Inline-Übersetzungseditor auf Marketing-Seiten (?edit=1, nur Admins).
   */
    'i18n_inline_editor' => (bool) env('APP_I18N_INLINE_EDITOR', false),

    /**
     * Plattform-weit aktivierte Sprachen (Marketing, Hub, Branding-DB).
     */
    'platform_locales' => [
        'de' => ['native' => 'Deutsch', 'flag' => '🇩🇪'],
        'en' => ['native' => 'English', 'flag' => '🇬🇧'],
        'fr' => ['native' => 'Français', 'flag' => '🇫🇷'],
        'it' => ['native' => 'Italiano', 'flag' => '🇮🇹'],
    ],

    /**
     * Creator-Hub (/hub/*, Auth, öffentliche Bio): verfügbare Oberflächensprachen.
     */
    'hub_locales' => [
        'de' => ['native' => 'Deutsch', 'flag' => '🇩🇪'],
        'en' => ['native' => 'English', 'flag' => '🇬🇧'],
        'fr' => ['native' => 'Français', 'flag' => '🇫🇷'],
        'it' => ['native' => 'Italiano', 'flag' => '🇮🇹'],
    ],

    /**
     * Profilbild: großzügiger Upload, Speicherung als verkleinertes WebP/JPEG.
     */
    'avatar' => [
        'max_upload_kb' => 8192,
        'max_edge_px' => 512,
        'jpeg_quality' => 85,
        'webp_quality' => 85,
    ],

    /**
     * Wallpaper- und Banner-Bilder für den Design-Tab.
     */
    'profile_images' => [
        'max_upload_kb' => 8192,
        'wallpaper_max_edge_px' => 1920,
        'banner_max_edge_px' => 1600,
        'jpeg_quality' => 85,
        'webp_quality' => 85,
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
