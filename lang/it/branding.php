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
        'eyebrow' => 'Link-in-bio for creators',
        'headline' => 'One link. Every channel. More reach.',
        'subline' => 'Build a clear bio page in minutes with measurable clicks — control themes and copy in one place.',
        'cta_primary' => 'Start for free',
        'cta_secondary' => 'View pricing',
        'footer_tagline' => 'Bio pages for creators & brands — fast, measurable, low-friction.',
        'trust_strip' => 'Share everywhere your community hangs out',
        'trust_count' => '12,000+',
        'trust_count_label' => 'bio pages live — from podcasts to brands',
        'home_templates_title' => 'Free templates',
        'home_templates_subline' => 'Pick a layout and palette — your page looks polished instantly.',
        'final_cta_title' => 'Ready for your link-in-bio moment?',
        'final_cta_subline' => 'Sign up, pick a theme, share one URL everywhere.',
        'final_cta_button' => 'Get started',

        'steps' => [
            '1' => [
                'title' => 'Create',
                'text' => 'Manage links, avatar, and bio in one dashboard.',
            ],
            '2' => [
                'title' => 'Style',
                'text' => 'Tune themes and layouts — light, dark, or bold.',
            ],
            '3' => [
                'title' => 'Share',
                'text' => 'One URL for Instagram, TikTok, YouTube & more — updates go live instantly.',
            ],
        ],

        'features' => [
            '1' => [
                'title' => 'Smart links',
                'text' => 'Optional click tracking with a clean overview of what converts.',
            ],
            '2' => [
                'title' => 'Themes',
                'text' => 'Dozens of presets plus real layout styles: buttons, cards, backgrounds.',
            ],
            '3' => [
                'title' => 'Built to scale',
                'text' => 'Stripe billing, analytics, and admin settings — SaaS-ready.',
            ],
        ],

        'cards' => [
            '1' => [
                'title' => 'Create',
                'text' => 'Build your bio page fast: avatar, copy, buttons — done.',
                'icon' => '✦',
            ],
            '2' => [
                'title' => 'Integrate',
                'text' => 'Shop, newsletter, podcast, socials — all behind one link.',
                'icon' => '🔗',
            ],
            '3' => [
                'title' => 'Share',
                'text' => 'A short URL for bios, stories, and campaigns — updates in seconds.',
                'icon' => '🚀',
            ],
        ],
    ],

    'bio' => [
        'cta_label_default' => 'Open',
        'platform_credit' => 'Built with',
        'platform_url_label' => 'Home',
        'cookie_text' => 'We use essential cookies for login, security, and analytics. See the privacy policy for details.',
        'cookie_button' => 'Got it',
    ],

    'footer' => [
        'brand_label' => 'About',
        'nav_label' => 'Navigation',
        'legal_label' => 'Legal',
    ],

    'faq' => [
        'title' => 'FAQ',
        'items' => [
            [
                'question' => 'How does click tracking work?',
                'answer' => 'Visitors tap a smart link that routes through a tracking URL. We count the click (no raw IP storage) and redirect.',
            ],
            [
                'question' => 'Can I upgrade later?',
                'answer' => 'Yes — billing runs through Stripe Checkout and the customer portal.',
            ],
            [
                'question' => 'Where do I get help?',
                'answer' => 'Use the Help page or the dashboard sections for Links, Bio page, and Analytics.',
            ],
        ],
    ],

    'help' => [
        'title' => 'Help & support',
        'intro' => 'Quick answers for common tasks in the hub.',
        'sections' => [
            [
                'heading' => 'Getting started',
                'body' => 'After sign-up, open Bio page to set avatar, copy, and links. Save and publish — your public URL is ready.',
            ],
            [
                'heading' => 'Links & tracking',
                'body' => 'Under Links you can enable smart links with tracking. Analytics shows clicks at a glance.',
            ],
            [
                'heading' => 'Support',
                'body' => 'Operators can add support email and channels in admin settings.',
            ],
        ],
    ],

    'pricing' => [
        'title' => 'Simple pricing',
        'subline' => 'Start free — upgrade when you need more.',
        'plans' => [
            'free' => [
                'name' => 'Free',
                'price' => '€0',
                'period' => '',
                'features' => [
                    '1 profile',
                    'Up to 10 links',
                    'Basic analytics',
                    'Platform branding',
                ],
                'cta' => 'Get started',
            ],
            'starter' => [
                'name' => 'Starter',
                'price' => '€9',
                'period' => 'month',
                'features' => [
                    'Unlimited links',
                    'No platform branding',
                    'Custom domain (roadmap)',
                ],
                'cta' => 'Upgrade in dashboard',
            ],
            'pro' => [
                'name' => 'Pro',
                'price' => '€24',
                'period' => 'month',
                'features' => [
                    'Everything in Starter',
                    'Referrals & rewards (roadmap)',
                    'UTM & conversion (roadmap)',
                ],
                'cta' => 'Upgrade in dashboard',
            ],
        ],
    ],

    'legal' => [
        'impressum_html' => <<<'MD'
## Legal notice

Placeholder information under applicable law — **replace** with legal entity, address, and contact before production (Admin → Branding → Legal).

- **Name:** …
- **Address:** …
- **Email:** …
- **VAT ID:** …
MD,
        'datenschutz_html' => <<<'MD'
## Privacy policy

**Placeholder.** Describe processing of personal data, cookies, hosting, and data subject rights — replace before production (Admin → Branding → Legal).
MD,
        'agb_html' => <<<'MD'
## Terms of service

**Placeholder.** Scope, services, payment, termination — replace with compliant terms before production (Admin → Branding → Legal).
MD,
    ],
];
