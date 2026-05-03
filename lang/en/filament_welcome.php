<?php

return [
    'title' => 'Welcome to the admin area',
    'intro' => 'Short guide to the main sections. You can hide this box permanently with the button below.',

    'sections' => [
        'dashboard' => [
            'title' => 'Dashboard',
            'body' => 'Your home after sign-in: overview and account widget. Use the sidebar to open other areas.',
        ],
        'profiles' => [
            'title' => 'Profiles',
            'body' => 'Public link-in-bio pages: slug, theme, links, and visibility. Each profile belongs to a workspace.',
        ],
        'users' => [
            'title' => 'Users',
            'body' => 'Platform accounts (creators): e-mail, verification, admin flag, and link to their workspaces.',
        ],
        'workspaces' => [
            'title' => 'Workspaces',
            'body' => 'Billing and plan context per customer: plan, limits, suspension, and which profiles belong here.',
        ],
    ],

    'billing_title' => 'Stripe & plans',
    'billing_stripe' => 'Enter your Stripe API keys and price IDs in the server `.env` (for example `STRIPE_KEY`, `STRIPE_SECRET`, `STRIPE_WEBHOOK_SECRET`, and the price variables referenced in the app). After changes, reload the config cache on the server.',
    'billing_plans' => 'Adjust free/starter/pro behaviour (limits, branding, which Stripe price applies) in `config/creator.php` under `plans` and `stripe_prices` — then deploy and clear config cache if you use `php artisan config:cache`.',

    'dismiss' => 'Got it — hide permanently',
];
