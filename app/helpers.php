<?php

use App\Models\Profile;
use App\Services\BrandingService;
use App\Support\ProfileDesignSettings;
use Illuminate\Support\Facades\Storage;

if (! function_exists('clh_inline_editing_enabled')) {
    function clh_inline_editing_enabled(): bool
    {
        if (! config('creator.i18n_inline_editor', false)) {
            return false;
        }

        $user = auth()->user();

        if ($user === null || ! (bool) $user->is_admin) {
            return false;
        }

        if (request()->boolean('edit')) {
            session(['clh_inline_edit' => true]);
        }

        return request()->boolean('edit')
            || session('clh_inline_edit') === true
            || request()->cookie('clh_inline_edit') === '1';
    }
}

if (! function_exists('clh_inline_editor_assets')) {
    /** Livewire-Assets für Inline-Editor (Admin + Feature-Flag). */
    function clh_inline_editor_assets(): bool
    {
        if (! config('creator.i18n_inline_editor', false)) {
            return false;
        }

        $user = auth()->user();

        return $user !== null && (bool) $user->is_admin;
    }
}

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
     *     header_layout: string,
     *     wallpaper_style: string,
     * }
     */
    function clh_public_theme(Profile $profile): array
    {
        $settings = ProfileDesignSettings::fromProfile($profile);
        $theme = $profile->theme;
        $cardStyle = $theme?->card_style ?? 'flat';
        $fontKey = $settings->fontFamily;
        $headerLayout = $settings->headerLayout;

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

        $bodyStyle = match ($settings->wallpaperStyle) {
            'gradient' => 'font-family: '.$fontFamily.'; color: var(--clh-text); background: linear-gradient('.$settings->wallpaperGradientAngle.'deg, '.$settings->wallpaperGradientFrom.' 0%, '.$settings->wallpaperGradientTo.' 100%);',
            'image' => 'font-family: '.$fontFamily.'; color: var(--clh-text); background-color: '.$settings->wallpaperColor.';',
            default => 'font-family: '.$fontFamily.'; color: var(--clh-text); background-color: '.$settings->wallpaperColor.';',
        };

        $styleClass = match ($settings->buttonStyle) {
            'outline' => 'clh-link--style-outline',
            'glass' => 'clh-link--style-glass',
            default => 'clh-link--style-solid',
        };

        $shapeClass = match ($settings->buttonShape) {
            'square' => 'clh-link--shape-square',
            'rounded' => 'clh-link--shape-rounded',
            default => 'clh-link--shape-pill',
        };

        $shadowClass = match ($settings->buttonShadow) {
            'none' => 'clh-link--shadow-none',
            'strong' => 'clh-link--shadow-strong',
            'hard' => 'clh-link--shadow-hard',
            default => 'clh-link--shadow-soft',
        };

        $linkClasses = trim('clh-link '.$styleClass.' '.$shapeClass.' '.$shadowClass.($cardStyle === 'bordered' ? ' clh-link--card-bordered' : ''));

        $avatarRadius = $settings->buttonShape === 'square' ? '12px' : '9999px';
        $avatarSize = $headerLayout === 'hero' ? '9rem' : '7rem';

        return [
            'font_href' => $fontHref,
            'font_family' => $fontFamily,
            'body_style' => $bodyStyle,
            'pattern_overlay' => '',
            'link_class' => $linkClasses,
            'link_style' => '',
            'avatar_class' => 'mx-auto object-cover shadow-xl',
            'avatar_style' => 'height: '.$avatarSize.'; width: '.$avatarSize.'; border-radius: '.$avatarRadius.'; box-shadow: 0 0 0 4px color-mix(in srgb, var(--clh-accent) 38%, transparent); border: 3px solid color-mix(in srgb, var(--clh-accent) 60%, transparent);',
            'placeholder_avatar_class' => 'mx-auto flex items-center justify-center font-bold shadow-xl',
            'placeholder_avatar_style' => 'height: '.$avatarSize.'; width: '.$avatarSize.'; border-radius: '.$avatarRadius.'; background: color-mix(in srgb, var(--clh-card) 90%, transparent); color: var(--clh-accent); border: 3px solid color-mix(in srgb, var(--clh-accent) 50%, transparent); box-shadow: 0 0 0 4px color-mix(in srgb, var(--clh-accent) 28%, transparent);',
            'button_style' => $settings->buttonStyle,
            'card_style' => $cardStyle,
            'background_style' => $settings->wallpaperStyle,
            'header_layout' => $headerLayout,
            'wallpaper_style' => $settings->wallpaperStyle,
            'font_title_color' => $settings->fontTitleColor,
            'font_text_color' => $settings->fontTextColor,
            'button_bg' => $settings->buttonColor,
            'button_fg' => $settings->buttonTextColor,
        ];
    }
}

if (! function_exists('clh_public_presentation')) {
    /**
     * CSS-Variablen und Asset-URLs für öffentliche Bio (Layout + Vorschau).
     *
     * @return array{
     *     clh: array<string, mixed>,
     *     settings: ProfileDesignSettings,
     *     css_vars: array<string, string>,
     *     avatar_url: string|null,
     *     wallpaper_url: string|null,
     * }
     */
    function clh_public_presentation(Profile $profile): array
    {
        $clh = clh_public_theme($profile);
        $settings = ProfileDesignSettings::fromProfile($profile);
        $fallback = app(BrandingService::class)->profileThemeFallbackVariables();
        $vars = array_merge(
            $fallback,
            $profile->theme?->variables ?? [],
            is_array($profile->theme_variables) ? $profile->theme_variables : []
        );

        $accent = (string) ($vars['accent'] ?? $fallback['accent']);

        return [
            'clh' => $clh,
            'settings' => $settings,
            'css_vars' => [
                '--clh-bg' => $settings->wallpaperColor,
                '--clh-text' => $settings->fontTextColor,
                '--clh-title' => $settings->fontTitleColor,
                '--clh-accent' => $accent,
                '--clh-card' => $settings->buttonColor,
                '--clh-border' => (string) ($vars['border'] ?? $fallback['border']),
                '--clh-button-bg' => $settings->buttonColor,
                '--clh-button-fg' => $settings->buttonTextColor,
                '--clh-text-muted' => (string) ($vars['text_muted'] ?? $fallback['text_muted']),
            ],
            'avatar_url' => $profile->avatar_path
                ? Storage::url($profile->avatar_path)
                : null,
            'wallpaper_url' => $profile->wallpaper_image_path
                ? Storage::url($profile->wallpaper_image_path)
                : null,
        ];
    }
}
