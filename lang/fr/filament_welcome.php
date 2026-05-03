<?php

return [
    'title' => 'Bienvenue dans l’administration',
    'intro' => 'Aperçu des sections principales. Vous pouvez masquer définitivement ce cadre avec le bouton ci-dessous.',

    'sections' => [
        'dashboard' => [
            'title' => 'Dashboard',
            'body' => 'Page d’accueil après connexion : vue d’ensemble et widget de compte. Utilisez la barre latérale pour les autres zones.',
        ],
        'profiles' => [
            'title' => 'Profiles',
            'body' => 'Pages link-in-bio publiques : slug, thème, liens et visibilité. Chaque profil appartient à un workspace.',
        ],
        'users' => [
            'title' => 'Users',
            'body' => 'Comptes plateforme (créateurs) : e-mail, vérification, droits admin et rattachement aux workspaces.',
        ],
        'workspaces' => [
            'title' => 'Workspaces',
            'body' => 'Contexte facturation et offre par client : offre, limites, suspension et profils associés.',
        ],
    ],

    'billing_title' => 'Stripe & offres',
    'billing_stripe' => 'Saisissez vos clés API Stripe et les IDs de prix dans le fichier `.env` du serveur (`STRIPE_KEY`, `STRIPE_SECRET`, `STRIPE_WEBHOOK_SECRET`, variables de prix référencées par l’app). Après modification, rechargez ou videz le cache de configuration.',
    'billing_plans' => 'Ajustez gratuit/starter/pro (limites, marque, prix Stripe) dans `config/creator.php`, sections `plans` et `stripe_prices`, puis déployez et mettez à jour le cache si vous utilisez `php artisan config:cache`.',

    'dismiss' => 'Compris — masquer définitivement',
];
