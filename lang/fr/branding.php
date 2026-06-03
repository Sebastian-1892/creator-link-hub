<?php

return [
    'brand_name' => 'Creator Link Hub',

    'colors' => [
        'primary' => '#dc4b3f',
        'primary_contrast' => '#ffffff',
        'accent' => '#1f8b5a',
        'bg' => '#fdfaf6',
        'bg_alt' => '#fff5e6',
        'text' => '#1a1a1a',
        'text_muted' => '#6b6b6b',
        'card' => '#ffffff',
        'border' => '#e8e2d5',
    ],

    'marketing' => [
        'eyebrow' => 'Link-in-bio pour les créateurs',
        'headline' => 'Un lien. Tous les canaux. Plus de portée.',
        'subline' => 'Créez une page bio claire en quelques minutes avec des clics mesurables — thèmes et textes au même endroit.',
        'cta_primary' => 'Commencer gratuitement',
        'cta_secondary' => 'Voir les tarifs',
        'footer_tagline' => 'Pages bio pour créateurs et marques — rapides, mesurables, sans friction.',
        'trust_strip' => 'Partagez partout où vit votre communauté',
        'trust_count' => '12 000+',
        'trust_count_label' => 'pages bio en ligne — du podcast à la marque',
        'home_templates_title' => 'Modèles gratuits',
        'home_templates_subline' => 'Choisissez une mise en page et une palette — votre page a tout de suite un look pro.',
        'features_heading' => 'Pourquoi :name ?',
        'all_templates_link' => 'Tous les modèles dans le tableau de bord',
        'final_cta_title' => 'Prêt pour votre moment link-in-bio ?',
        'final_cta_subline' => 'Inscrivez-vous, choisissez un thème, partagez une URL partout.',
        'final_cta_button' => 'Commencer',

        'steps' => [
            '1' => [
                'title' => 'Créer',
                'text' => 'Gérez liens, avatar et bio dans un seul tableau de bord.',
            ],
            '2' => [
                'title' => 'Styliser',
                'text' => 'Ajustez thèmes et mises en page — clair, sombre ou audacieux.',
            ],
            '3' => [
                'title' => 'Partager',
                'text' => 'Une URL pour Instagram, TikTok, YouTube et plus — les mises à jour sont instantanées.',
            ],
        ],

        'features' => [
            '1' => [
                'title' => 'Liens intelligents',
                'text' => 'Suivi des clics en option avec une vue claire de ce qui convertit.',
            ],
            '2' => [
                'title' => 'Thèmes',
                'text' => 'Des dizaines de presets et de vrais styles : boutons, cartes, arrière-plans.',
            ],
            '3' => [
                'title' => 'Évolutif',
                'text' => 'Facturation Stripe, analytics et réglages admin — prêt pour le SaaS.',
            ],
        ],

        'cards' => [
            '1' => [
                'title' => 'Créer',
                'text' => 'Construisez votre page bio vite : avatar, textes, boutons — c’est fait.',
                'icon' => '✦',
            ],
            '2' => [
                'title' => 'Intégrer',
                'text' => 'Boutique, newsletter, podcast, réseaux — tout derrière un lien.',
                'icon' => '🔗',
            ],
            '3' => [
                'title' => 'Partager',
                'text' => 'Une URL courte pour bio, stories et campagnes — mises à jour en secondes.',
                'icon' => '🚀',
            ],
        ],
    ],

    'bio' => [
        'cta_label_default' => 'Ouvrir',
        'platform_credit' => 'Créé avec',
        'platform_url_label' => 'Accueil',
        'cookie_text' => 'Nous utilisons des cookies essentiels pour la connexion, la sécurité et les analytics. Voir la politique de confidentialité.',
        'cookie_button' => 'Compris',
    ],

    'footer' => [
        'brand_label' => 'À propos',
        'nav_label' => 'Navigation',
        'legal_label' => 'Mentions légales',
    ],

    'faq' => [
        'title' => 'FAQ',
        'items' => [
            [
                'question' => 'Comment fonctionne le suivi des clics ?',
                'answer' => 'Les visiteurs cliquent sur un lien intelligent qui passe par une URL de suivi. Nous comptons le clic (sans stocker l’IP en clair) et redirigeons.',
            ],
            [
                'question' => 'Puis-je passer à une offre supérieure plus tard ?',
                'answer' => 'Oui — la facturation passe par Stripe Checkout et le portail client.',
            ],
            [
                'question' => 'Où trouver de l’aide ?',
                'answer' => 'Utilisez la page Aide ou les sections Liens, Page bio et Analytique du tableau de bord.',
            ],
        ],
    ],

    'help' => [
        'title' => 'Aide et support',
        'intro' => 'Réponses rapides aux tâches courantes dans le hub.',
        'sections' => [
            [
                'heading' => 'Premiers pas',
                'body' => 'Après inscription, ouvrez Page bio pour définir avatar, texte et liens. Enregistrez et publiez — votre URL publique est prête.',
            ],
            [
                'heading' => 'Liens et suivi',
                'body' => 'Sous Liens, activez les liens intelligents avec suivi. Analytique affiche les clics en un coup d’œil.',
            ],
            [
                'heading' => 'Support',
                'body' => 'Les opérateurs peuvent ajouter e-mail et canaux de support dans les réglages admin.',
            ],
        ],
    ],

    'pricing' => [
        'title' => 'Tarifs simples',
        'subline' => 'Commencez gratuitement — passez au niveau supérieur quand vous en avez besoin.',
        'plans' => [
            'free' => [
                'name' => 'Gratuit',
                'price' => '0 €',
                'period' => '',
                'features' => [
                    '1 profil',
                    'Jusqu’à 10 liens',
                    'Analytics de base',
                    'Branding plateforme',
                ],
                'cta' => 'Commencer',
            ],
            'starter' => [
                'name' => 'Starter',
                'price' => '9 €',
                'period' => 'mois',
                'features' => [
                    'Liens illimités',
                    'Sans branding plateforme',
                    'Domaine personnalisé (feuille de route)',
                ],
                'cta' => 'Mettre à niveau dans le tableau de bord',
            ],
            'pro' => [
                'name' => 'Pro',
                'price' => '24 €',
                'period' => 'mois',
                'features' => [
                    'Tout Starter',
                    'Parrainage et récompenses (feuille de route)',
                    'UTM et conversion (feuille de route)',
                ],
                'cta' => 'Mettre à niveau dans le tableau de bord',
            ],
        ],
    ],

    'legal' => [
        'impressum_html' => <<<'MD'
## Mentions légales

Informations selon la loi applicable — **à remplacer** par la raison sociale, l’adresse et les contacts avant la mise en production (Admin → Branding → Juridique).

- **Nom :** …
- **Adresse :** …
- **E-mail :** …
- **N° TVA :** …
MD,
        'datenschutz_html' => <<<'MD'
## Politique de confidentialité

**Placeholder.** Décrivez le traitement des données personnelles, les cookies, l’hébergement et les droits des personnes — à remplacer avant la production (Admin → Branding → Juridique).
MD,
        'agb_html' => <<<'MD'
## Conditions générales

**Placeholder.** Objet, prestations, paiement, résiliation — à remplacer par des CGU conformes avant la production (Admin → Branding → Juridique).
MD,
    ],
];
