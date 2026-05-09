<?php

use App\Models\Profile;
use App\Services\BrandingService;

if (! function_exists('brand')) {
    /**
     * Übersetzter / gespeicherter Branding-Text (Schlüssel wie „marketing.headline“).
     */
    function brand(string $key, ?string $default = null): string
    {
        return app(BrandingService::class)->text($key, $default);
    }
}

if (! function_exists('brand_color')) {
    /**
     * Gespeicherte Branding-Farbe (Kurzschlüssel wie „primary“, „accent“).
     */
    function brand_color(string $shortKey, ?string $default = null): string
    {
        return app(BrandingService::class)->color($shortKey, $default);
    }
}

if (! function_exists('branding_payload')) {
    /**
     * Vollständiges Branding-Payload (Farben, Marketing, Bio, Logo).
     *
     * @return array<string, mixed>
     */
    function branding_payload(): array
    {
        return app(BrandingService::class)->payload();
    }
}

if (! function_exists('clh_public_theme')) {
    /**
     * Theme-Layout für öffentliche Bio-Seite (Schriften, Hintergrund, Button-/Karten-Stile).
     *
     * @return array{
     *     font_href: string,
     *     font_family: string,
     *     body_style: string,
     *     pattern_overlay: string,
     *     link_class: string,
     *     link_style: string,
     *     avatar_class: string,
     *     avatar_style: string,
     *     placeholder_avatar_class: string,
     *     placeholder_avatar_style: string,
     *     button_style: string,
     *     card_style: string,
     *     background_style: string,
     * }
     */
    function clh_public_theme(Profile $profile): array
    {
        $theme = $profile->theme;
        $buttonStyle = $theme?->button_style ?? 'pill';
        $backgroundStyle = $theme?->background_style ?? 'solid';
        $fontKey = $theme?->font_family ?? 'figtree';
        $cardStyle = $theme?->card_style ?? 'flat';

        $fontQueries = [
            'figtree' => 'figtree:400,500,600,700',
            'inter' => 'inter:400,500,600,700',
            'playfair' => 'playfair-display:400,500,600,700',
            'space-mono' => 'space-mono:400,700',
            'dm-sans' => 'dm-sans:400,500,600,700',
        ];
        $fontFamilies = [
            'figtree' => "'Figtree', ui-sans-serif, system-ui",
            'inter' => "'Inter', ui-sans-serif, system-ui",
            'playfair' => "'Playfair Display', ui-serif, Georgia, serif",
            'space-mono' => "'Space Mono', ui-monospace, monospace",
            'dm-sans' => "'DM Sans', ui-sans-serif, system-ui",
        ];

        $fq = $fontQueries[$fontKey] ?? $fontQueries['figtree'];
        $fontFamily = $fontFamilies[$fontKey] ?? $fontFamilies['figtree'];
        $fontHref = 'https://fonts.bunny.net/css?family='.$fq.'&display=swap';

        $dotSvg = rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" width="28" height="28"><circle cx="4" cy="4" r="1.25" fill="%23000000" opacity="0.06"/></svg>');
        $gridSvg = rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" width="40" height="40"><path d="M40 0H0V40" fill="none" stroke="%23000000" stroke-width="1" opacity="0.07"/></svg>');
        $noiseSvg = rawurlencode('<svg xmlns="http://www.w3.org/2000/svg" width="120" height="120"><filter id="n"><feTurbulence type="fractalNoise" baseFrequency="0.9" numOctaves="4" stitchTiles="stitch"/></filter><rect width="100%" height="100%" filter="url(%23n)" opacity="0.05"/></svg>');

        $bodyStyle = match ($backgroundStyle) {
            'gradient' => 'font-family: '.$fontFamily.'; color: var(--clh-text); background: linear-gradient(165deg, var(--clh-bg) 0%, color-mix(in srgb, var(--clh-accent) 22%, var(--clh-bg)) 45%, var(--clh-bg-alt) 100%);',
            'radial-glow' => 'font-family: '.$fontFamily.'; color: var(--clh-text); background: radial-gradient(ellipse 90% 55% at 50% -15%, color-mix(in srgb, var(--clh-accent) 35%, transparent), transparent 55%), linear-gradient(180deg, var(--clh-bg), color-mix(in srgb, var(--clh-bg) 88%, #000));',
            'pattern-dots' => 'font-family: '.$fontFamily.'; color: var(--clh-text); background-color: var(--clh-bg); background-image: url("data:image/svg+xml,'.$dotSvg.'"); background-size: 28px 28px;',
            'pattern-grid' => 'font-family: '.$fontFamily.'; color: var(--clh-text); background-color: var(--clh-bg); background-image: url("data:image/svg+xml,'.$gridSvg.'"); background-size: 40px 40px;',
            'noise' => 'font-family: '.$fontFamily.'; color: var(--clh-text); background-color: var(--clh-bg); background-image: url("data:image/svg+xml,'.$noiseSvg.'");',
            default => 'font-family: '.$fontFamily.'; color: var(--clh-text); background: radial-gradient(ellipse 90% 55% at 50% -15%, var(--clh-accent-soft), transparent 55%), linear-gradient(180deg, var(--clh-bg), color-mix(in srgb, var(--clh-bg) 92%, #fff));',
        };

        $radius = match ($buttonStyle) {
            'square' => '6px',
            'rounded', 'glass', 'shadow' => '16px',
            default => '9999px',
        };

        $linkStyle = match ($buttonStyle) {
            'outline' => 'background: transparent; color: var(--clh-accent); border: 2px solid color-mix(in srgb, var(--clh-accent) 85%, transparent); border-radius: '.$radius.';',
            'glass' => 'background: color-mix(in srgb, var(--clh-card) 45%, transparent); color: var(--clh-text); border: 1px solid color-mix(in srgb, var(--clh-border) 70%, transparent); border-radius: '.$radius.'; backdrop-filter: blur(14px); -webkit-backdrop-filter: blur(14px);',
            'shadow' => 'background: var(--clh-card); color: var(--clh-text); border: 1px solid var(--clh-border); border-radius: '.$radius.'; box-shadow: 0 14px 40px rgba(0,0,0,0.28);',
            default => 'background: var(--clh-card); color: var(--clh-text); border: 1px solid var(--clh-border); border-radius: '.$radius.';',
        };

        $cardShadow = match ($cardStyle) {
            'elevated' => 'box-shadow: 0 16px 42px rgba(0,0,0,0.14);',
            'glass' => 'backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); background: color-mix(in srgb, var(--clh-card) 65%, transparent) !important;',
            'bordered' => 'border-width: 2px;',
            'pill' => 'border-radius: 28px;',
            default => '',
        };

        if ($cardShadow !== '' && ! str_contains($linkStyle, 'box-shadow') && $cardStyle === 'elevated') {
            $linkStyle .= ' '.$cardShadow;
        }
        if ($cardStyle === 'glass' && $buttonStyle !== 'glass') {
            $linkStyle .= ' backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); background: color-mix(in srgb, var(--clh-card) 55%, transparent);';
        }
        if ($cardStyle === 'bordered') {
            $linkStyle .= ' border-width: 2px; border-color: var(--clh-accent);';
        }
        if ($cardStyle === 'pill') {
            $linkStyle .= ' border-radius: 28px;';
        }

        $avatarRadius = $buttonStyle === 'square' ? '12px' : '9999px';

        return [
            'font_href' => $fontHref,
            'font_family' => $fontFamily,
            'body_style' => $bodyStyle,
            'pattern_overlay' => '',
            'link_class' => 'group relative flex w-full items-center gap-3 px-5 py-4 font-semibold transition duration-200 hover:-translate-y-0.5 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2',
            'link_style' => $linkStyle,
            'avatar_class' => 'mx-auto object-cover shadow-xl',
            'avatar_style' => 'height: 7rem; width: 7rem; border-radius: '.$avatarRadius.'; box-shadow: 0 0 0 4px color-mix(in srgb, var(--clh-accent) 38%, transparent); border: 3px solid color-mix(in srgb, var(--clh-accent) 60%, transparent);',
            'placeholder_avatar_class' => 'mx-auto flex h-28 w-28 items-center justify-center font-bold shadow-xl',
            'placeholder_avatar_style' => 'border-radius: '.$avatarRadius.'; background: color-mix(in srgb, var(--clh-card) 90%, transparent); color: var(--clh-accent); border: 3px solid color-mix(in srgb, var(--clh-accent) 50%, transparent); box-shadow: 0 0 0 4px color-mix(in srgb, var(--clh-accent) 28%, transparent);',
            'button_style' => $buttonStyle,
            'card_style' => $cardStyle,
            'background_style' => $backgroundStyle,
        ];
    }
}
