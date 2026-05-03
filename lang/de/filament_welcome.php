<?php

return [
    'title' => 'Willkommen im Adminbereich',
    'intro' => 'Kurzüberblick über die wichtigsten Bereiche. Du kannst diese Box unten dauerhaft ausblenden.',

    'sections' => [
        'dashboard' => [
            'title' => 'Dashboard',
            'body' => 'Deine Startseite nach dem Login: Überblick und Konto-Widget. Über die Seitenleiste erreichst du die weiteren Bereiche.',
        ],
        'profiles' => [
            'title' => 'Profiles',
            'body' => 'Öffentliche Link-in-Bio-Seiten: Slug, Theme, Links und Sichtbarkeit. Jedes Profil gehört zu einem Workspace.',
        ],
        'users' => [
            'title' => 'Users',
            'body' => 'Plattform-Konten (Creators): E-Mail, Verifizierung, Admin-Kennzeichen und Zuordnung zu Workspaces.',
        ],
        'workspaces' => [
            'title' => 'Workspaces',
            'body' => 'Abrechnungs- und Plan-Kontext pro Kundin/Kunde: Plan, Limits, Sperrung und welche Profile dazu gehören.',
        ],
    ],

    'billing_title' => 'Stripe & Pakete',
    'billing_stripe' => 'Trage deine Stripe-API-Schlüssel und Preis-IDs in der Server-`.env` ein (z. B. `STRIPE_KEY`, `STRIPE_SECRET`, `STRIPE_WEBHOOK_SECRET` sowie die im Projekt verwendeten Preis-Variablen). Nach Änderungen auf dem Server den Konfigurations-Cache neu aufbauen bzw. leeren.',
    'billing_plans' => 'Passe Free/Starter/Pro an (Limits, Branding, welcher Stripe-Preis gilt) in `config/creator.php` unter `plans` und `stripe_prices` an — danach deployen und bei `config:cache` den Cache aktualisieren.',

    'dismiss' => 'Verstanden — dauerhaft ausblenden',
];
