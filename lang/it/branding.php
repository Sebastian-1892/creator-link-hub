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
        'eyebrow' => 'Link-in-bio per creator',
        'headline' => 'Un link. Tutti i canali. Più reach.',
        'subline' => 'Crea una pagina bio chiara in pochi minuti con click misurabili — temi e testi in un unico posto.',
        'cta_primary' => 'Inizia gratis',
        'cta_secondary' => 'Vedi prezzi',
        'footer_tagline' => 'Pagine bio per creator e brand — veloci, misurabili, senza attrito.',
        'trust_strip' => 'Condividi ovunque sia la tua community',
        'trust_count' => '12.000+',
        'trust_count_label' => 'pagine bio online — dal podcast al brand',
        'home_templates_title' => 'Template gratuiti',
        'home_templates_subline' => 'Scegli layout e palette — la pagina ha subito un look professionale.',
        'features_heading' => 'Perché :name?',
        'all_templates_link' => 'Tutti i modelli nella dashboard',
        'final_cta_title' => 'Pronto per il tuo momento link-in-bio?',
        'final_cta_subline' => 'Registrati, scegli un tema, condividi un URL ovunque.',
        'final_cta_button' => 'Inizia',

        'steps' => [
            '1' => [
                'title' => 'Crea',
                'text' => 'Gestisci link, avatar e bio in un’unica dashboard.',
            ],
            '2' => [
                'title' => 'Stile',
                'text' => 'Regola temi e layout — chiaro, scuro o audace.',
            ],
            '3' => [
                'title' => 'Condividi',
                'text' => 'Un URL per Instagram, TikTok, YouTube e altro — gli aggiornamenti sono immediati.',
            ],
        ],

        'features' => [
            '1' => [
                'title' => 'Smart link',
                'text' => 'Tracciamento click opzionale con panoramica di cosa converte.',
            ],
            '2' => [
                'title' => 'Temi',
                'text' => 'Decine di preset e stili reali: pulsanti, card, sfondi.',
            ],
            '3' => [
                'title' => 'Scalabile',
                'text' => 'Fatturazione Stripe, analytics e impostazioni admin — pronto per il SaaS.',
            ],
        ],

        'cards' => [
            '1' => [
                'title' => 'Crea',
                'text' => 'Costruisci la pagina bio in fretta: avatar, testi, pulsanti — fatto.',
                'icon' => '✦',
            ],
            '2' => [
                'title' => 'Integra',
                'text' => 'Shop, newsletter, podcast, social — tutto dietro un link.',
                'icon' => '🔗',
            ],
            '3' => [
                'title' => 'Condividi',
                'text' => 'Un URL breve per bio, storie e campagne — aggiornamenti in secondi.',
                'icon' => '🚀',
            ],
        ],
    ],

    'bio' => [
        'cta_label_default' => 'Apri',
        'platform_credit' => 'Creato con',
        'platform_url_label' => 'Home',
        'cookie_text' => 'Usiamo cookie essenziali per login, sicurezza e analytics. Dettagli nell’informativa privacy.',
        'cookie_button' => 'OK',
    ],

    'footer' => [
        'brand_label' => 'Chi siamo',
        'nav_label' => 'Navigazione',
        'legal_label' => 'Note legali',
    ],

    'faq' => [
        'title' => 'FAQ',
        'items' => [
            [
                'question' => 'Come funziona il tracciamento dei click?',
                'answer' => 'I visitatori cliccano un smart link che passa da un URL di tracciamento. Contiamo il click (senza memorizzare l’IP in chiaro) e reindirizziamo.',
            ],
            [
                'question' => 'Posso fare upgrade in seguito?',
                'answer' => 'Sì — la fatturazione passa da Stripe Checkout e dal portale clienti.',
            ],
            [
                'question' => 'Dove trovo aiuto?',
                'answer' => 'Usa la pagina Aiuto o le sezioni Link, Pagina bio e Analytics nella dashboard.',
            ],
        ],
    ],

    'help' => [
        'title' => 'Aiuto e supporto',
        'intro' => 'Risposte rapide alle attività comuni nell’hub.',
        'sections' => [
            [
                'heading' => 'Primi passi',
                'body' => 'Dopo la registrazione apri Pagina bio per avatar, testo e link. Salva e pubblica — l’URL pubblico è pronto.',
            ],
            [
                'heading' => 'Link e tracciamento',
                'body' => 'In Link puoi attivare smart link con tracciamento. In Analytics vedi i click a colpo d’occhio.',
            ],
            [
                'heading' => 'Supporto',
                'body' => 'Gli operatori possono aggiungere email e canali di supporto nelle impostazioni admin.',
            ],
        ],
    ],

    'pricing' => [
        'title' => 'Prezzi semplici',
        'subline' => 'Inizia gratis — passa al piano superiore quando serve.',
        'plans' => [
            'free' => [
                'name' => 'Gratis',
                'price' => '0 €',
                'period' => '',
                'features' => [
                    '1 profilo',
                    'Fino a 10 link',
                    'Analytics base',
                    'Branding piattaforma',
                ],
                'cta' => 'Inizia',
            ],
            'starter' => [
                'name' => 'Starter',
                'price' => '9 €',
                'period' => 'mese',
                'features' => [
                    'Link illimitati',
                    'Senza branding piattaforma',
                    'Dominio personalizzato (roadmap)',
                ],
                'cta' => 'Upgrade in dashboard',
            ],
            'pro' => [
                'name' => 'Pro',
                'price' => '24 €',
                'period' => 'mese',
                'features' => [
                    'Tutto Starter',
                    'Referral e premi (roadmap)',
                    'UTM e conversione (roadmap)',
                ],
                'cta' => 'Upgrade in dashboard',
            ],
        ],
    ],

    'legal' => [
        'impressum_html' => <<<'MD'
## Note legali

Informazioni secondo la legge applicabile — **sostituire** con ragione sociale, indirizzo e contatti prima della produzione (Admin → Branding → Legale).

- **Nome:** …
- **Indirizzo:** …
- **E-mail:** …
- **P. IVA:** …
MD,
        'datenschutz_html' => <<<'MD'
## Informativa privacy

**Placeholder.** Descrivi il trattamento dei dati personali, cookie, hosting e diritti degli interessati — sostituire prima della produzione (Admin → Branding → Legale).
MD,
        'agb_html' => <<<'MD'
## Termini di servizio

**Placeholder.** Oggetto, servizi, pagamento, recesso — sostituire con termini conformi prima della produzione (Admin → Branding → Legale).
MD,
    ],
];
