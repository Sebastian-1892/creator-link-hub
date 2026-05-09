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
        'eyebrow' => 'Link-in-Bio für Creator',
        'headline' => 'Ein Link. Alle Kanäle. Mehr Reichweite.',
        'subline' => 'Erstelle in Minuten eine klare Bio-Seite mit messbaren Klicks — Themes und Texte steuerst du zentral.',
        'cta_primary' => 'Kostenlos starten',
        'cta_secondary' => 'Preise ansehen',
        'footer_tagline' => 'Die Bio-Page für Creator & Brands — schnell, messbar, ohne Tech-Stress.',
        'trust_strip' => 'Überall dort teilen, wo deine Community ist',
        'trust_count' => '12.000+',
        'trust_count_label' => 'Bio-Seiten live — von Podcast bis Brand',
        'home_templates_title' => 'Kostenlose Vorlagen',
        'home_templates_subline' => 'Wähle ein Layout und Farben — so sieht deine Seite sofort professionell aus.',
        'final_cta_title' => 'Bereit für deinen Hey-Link-Moment?',
        'final_cta_subline' => 'Registriere dich, wähle ein Theme und teile eine URL — überall.',
        'final_cta_button' => 'Jetzt loslegen',

        'steps' => [
            '1' => [
                'title' => 'Erstellen',
                'text' => 'Links, Bild und Bio zentral verwalten — ein Dashboard für alle Ziele.',
            ],
            '2' => [
                'title' => 'Gestalten',
                'text' => 'Themes und Layouts abstimmen: hell, dunkel oder knallig — wie deine Marke.',
            ],
            '3' => [
                'title' => 'Teilen',
                'text' => 'Eine URL für Instagram, TikTok, YouTube & Co. Updates wirken sofort überall.',
            ],
        ],

        'features' => [
            '1' => [
                'title' => 'Smart Links',
                'text' => 'Optionales Click-Tracking mit Übersicht — sieh, welche Buttons konvertieren.',
            ],
            '2' => [
                'title' => 'Themes',
                'text' => 'Dutzende Presets plus echte Layout-Stile: Buttons, Karten, Hintergründe.',
            ],
            '3' => [
                'title' => 'Skalierbar',
                'text' => 'Stripe-Billing, Analytics und Admin-Einstellungen — SaaS-ready.',
            ],
        ],

        'cards' => [
            '1' => [
                'title' => 'Erstellen',
                'text' => 'Baue deine Bio-Seite mit Drag-and-feel: Avatar, Texte, Buttons — fertig.',
                'icon' => '✦',
            ],
            '2' => [
                'title' => 'Integrieren',
                'text' => 'Shop, Newsletter, Podcast und Socials — alles hinter einem Link.',
                'icon' => '🔗',
            ],
            '3' => [
                'title' => 'Teilen',
                'text' => 'Eine kurze URL für Stories, Bio und Kampagnen — Updates in Sekunden live.',
                'icon' => '🚀',
            ],
        ],
    ],

    'bio' => [
        'cta_label_default' => 'Öffnen',
        'platform_credit' => 'Erstellt mit',
        'platform_url_label' => 'Startseite',
        'cookie_text' => 'Wir verwenden notwendige Cookies für Login, Sicherheit und Analytics. Details in der Datenschutzerklärung.',
        'cookie_button' => 'Verstanden',
    ],

    'footer' => [
        'brand_label' => 'Über uns',
        'nav_label' => 'Navigation',
        'legal_label' => 'Rechtliches',
    ],

    'faq' => [
        'title' => 'Häufige Fragen',
        'items' => [
            [
                'question' => 'Wie funktioniert Click-Tracking?',
                'answer' => 'Besucher klicken auf einen Smart-Link, der über eine Tracking-URL läuft. Wir zählen den Klick (ohne Klartext-IP) und leiten weiter.',
            ],
            [
                'question' => 'Kann ich später upgraden?',
                'answer' => 'Ja — Abrechnung läuft über Stripe Checkout und Kundenportal.',
            ],
            [
                'question' => 'Wo finde ich Hilfe?',
                'answer' => 'Nutze die Hilfe-Seite oder das Dashboard unter „Links“, „Bio-Seite“ und „Analytics“.',
            ],
        ],
    ],

    'help' => [
        'title' => 'Hilfe & Support',
        'intro' => 'Kurze Antworten zu den wichtigsten Aufgaben im Hub.',
        'sections' => [
            [
                'heading' => 'Erste Schritte',
                'body' => 'Nach der Registrierung legst du unter „Bio-Seite“ Avatar, Text und Links an. Speichern und veröffentlichen — die öffentliche URL steht sofort bereit.',
            ],
            [
                'heading' => 'Links & Tracking',
                'body' => 'Unter „Links“ kannst du Smart-Links mit Tracking aktivieren. In „Analytics“ siehst du Klicks auf einen Blick.',
            ],
            [
                'heading' => 'Support',
                'body' => 'Support-E-Mail und weitere Kanäle kann der Betreiber in den Admin-Einstellungen hinterlegen.',
            ],
        ],
    ],

    'pricing' => [
        'title' => 'Einfache Preise',
        'subline' => 'Starte kostenlos — upgrade, wenn du mehr brauchst.',
        'plans' => [
            'free' => [
                'name' => 'Free',
                'price' => '0 €',
                'period' => '',
                'features' => [
                    '1 Profil',
                    'Bis zu 10 Links',
                    'Basis-Analytics',
                    'Plattform-Branding',
                ],
                'cta' => 'Starten',
            ],
            'starter' => [
                'name' => 'Starter',
                'price' => '9 €',
                'period' => 'Monat',
                'features' => [
                    'Unbegrenzte Links',
                    'Ohne Plattform-Branding',
                    'Eigene Domain (Roadmap)',
                ],
                'cta' => 'Upgrade im Dashboard',
            ],
            'pro' => [
                'name' => 'Pro',
                'price' => '24 €',
                'period' => 'Monat',
                'features' => [
                    'Alles aus Starter',
                    'Referrals & Rewards (Roadmap)',
                    'UTM & Conversion (Roadmap)',
                ],
                'cta' => 'Upgrade im Dashboard',
            ],
        ],
    ],

    'legal' => [
        'impressum_html' => <<<'MD'
## Impressum

Angaben gemäß § 5 TMG — **Platzhalter**. Bitte Firmendaten, Adresse und Kontakt vor Go-Live im Admin unter *Branding → Rechtliches* eintragen.

- **Name:** …
- **Adresse:** …
- **E-Mail:** …
- **USt-IdNr.:** …
MD,
        'datenschutz_html' => <<<'MD'
## Datenschutzerklärung

**Platzhalter.** Beschreibung der Verarbeitung personenbezogener Daten, Cookies, Hosting und Betroffenenrechte — bitte vor Produktion durch einen Datenschutz-Text ersetzen (Admin → Branding → Rechtliches).
MD,
        'agb_html' => <<<'MD'
## Allgemeine Geschäftsbedingungen (AGB)

**Platzhalter.** Vertragsgegenstand, Leistungen, Zahlung, Kündigung — bitte vor Produktion durch rechtskonforme AGB ersetzen (Admin → Branding → Rechtliches).
MD,
    ],
];
