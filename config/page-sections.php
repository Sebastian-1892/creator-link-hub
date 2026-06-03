<?php

return [
    'pages' => [
        'home' => [
            'hero' => [
                'label' => 'Hero',
                'sort_order' => 10,
                'render_type' => 'html',
                'blade_partial' => null,
            ],
            'trust' => [
                'label' => 'Trust',
                'sort_order' => 20,
                'render_type' => 'html',
                'blade_partial' => null,
            ],
            'cards' => [
                'label' => 'Icon-Karten',
                'sort_order' => 30,
                'render_type' => 'html',
                'blade_partial' => null,
            ],
            'mockup_strip' => [
                'label' => 'Live-Vorschau',
                'sort_order' => 40,
                'render_type' => 'blade',
                'blade_partial' => 'marketing.partials.home-mockup-strip',
            ],
            'features' => [
                'label' => 'Features',
                'sort_order' => 50,
                'render_type' => 'html',
                'blade_partial' => null,
            ],
            'templates_intro' => [
                'label' => 'Vorlagen (Intro)',
                'sort_order' => 60,
                'render_type' => 'html',
                'blade_partial' => null,
            ],
            'templates_carousel' => [
                'label' => 'Vorlagen (Karussell)',
                'sort_order' => 70,
                'render_type' => 'blade',
                'blade_partial' => 'marketing.partials.home-templates-carousel',
            ],
            'final_cta' => [
                'label' => 'Abschluss-CTA',
                'sort_order' => 80,
                'render_type' => 'html',
                'blade_partial' => null,
            ],
        ],
    ],
];
