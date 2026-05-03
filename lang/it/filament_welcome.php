<?php

return [
    'title' => 'Benvenuto nell’area di amministrazione',
    'intro' => 'Panoramica delle sezioni principali. Puoi nascondere definitivamente questo riquadro con il pulsante in basso.',

    'sections' => [
        'dashboard' => [
            'title' => 'Dashboard',
            'body' => 'Home dopo l’accesso: panoramica e widget account. Usa la barra laterale per le altre sezioni.',
        ],
        'profiles' => [
            'title' => 'Profiles',
            'body' => 'Pagine link-in-bio pubbliche: slug, tema, link e visibilità. Ogni profilo appartiene a un workspace.',
        ],
        'users' => [
            'title' => 'Users',
            'body' => 'Account della piattaforma (creator): e-mail, verifica, flag admin e collegamento ai workspace.',
        ],
        'workspaces' => [
            'title' => 'Workspaces',
            'body' => 'Contesto di fatturazione e piano per cliente: piano, limiti, sospensione e profili collegati.',
        ],
    ],

    'billing_title' => 'Stripe e piani',
    'billing_stripe' => 'Inserisci le chiavi API Stripe e gli ID prezzo nel file `.env` del server (`STRIPE_KEY`, `STRIPE_SECRET`, `STRIPE_WEBHOOK_SECRET` e le variabili prezzo usate dall’app). Dopo le modifiche, aggiorna o svuota la cache di configurazione sul server.',
    'billing_plans' => 'Adatta free/starter/pro (limiti, branding, prezzo Stripe) in `config/creator.php` nelle sezioni `plans` e `stripe_prices`, poi esegui il deploy e aggiorna la cache se usi `php artisan config:cache`.',

    'dismiss' => 'Ok — nascondi in modo permanente',
];
