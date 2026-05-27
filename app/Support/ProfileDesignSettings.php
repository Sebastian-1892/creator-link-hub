<?php

namespace App\Support;

use App\Models\Profile;
use App\Models\Theme;
use App\Services\BrandingService;

class ProfileDesignSettings
{
    public const FONT_FAMILIES = ['figtree', 'inter', 'playfair', 'space-mono', 'dm-sans'];

    public const HEADER_LAYOUTS = ['classic', 'hero', 'banner'];

    public const WALLPAPER_STYLES = ['solid', 'gradient', 'image'];

    public const BUTTON_STYLES = ['solid', 'glass', 'outline'];

    public const BUTTON_SHAPES = ['square', 'rounded', 'pill'];

    public const BUTTON_SHADOWS = ['none', 'soft', 'strong', 'hard'];

    public function __construct(
        public ?int $themeId = null,
        public string $themeFilter = 'all',
        public string $headerLayout = 'classic',
        public string $wallpaperStyle = 'solid',
        public string $wallpaperColor = '#f8fafc',
        public string $wallpaperGradientFrom = '#f8fafc',
        public string $wallpaperGradientTo = '#e2e8f0',
        public int $wallpaperGradientAngle = 165,
        public string $fontFamily = 'figtree',
        public string $fontTextColor = '#0f172a',
        public string $fontTitleColor = '#0f172a',
        public string $buttonStyle = 'solid',
        public string $buttonShape = 'pill',
        public string $buttonShadow = 'soft',
        public string $buttonColor = '#ffffff',
        public string $buttonTextColor = '#0f172a',
    ) {}

    /**
     * Design-Felder aus einer Theme-Vorlage (für Live-Vorschau nach Theme-Wechsel).
     */
    public static function fromTheme(Theme $theme, Profile $profile): self
    {
        $v = is_array($theme->variables) ? $theme->variables : [];
        $bg = (string) ($v['bg'] ?? '#f8fafc');
        $text = (string) ($v['text'] ?? '#0f172a');
        $accent = (string) ($v['accent'] ?? '#6366f1');
        $card = (string) ($v['card'] ?? '#ffffff');

        $wallpaperStyle = self::wallpaperStyleForTheme($theme, $profile);
        $fontFamily = in_array($theme->font_family, self::FONT_FAMILIES, true)
            ? $theme->font_family
            : 'figtree';

        return new self(
            themeId: $theme->id,
            headerLayout: (string) ($profile->header_layout ?: 'classic'),
            wallpaperStyle: $wallpaperStyle,
            wallpaperColor: $bg,
            wallpaperGradientFrom: $bg,
            wallpaperGradientTo: $accent,
            wallpaperGradientAngle: 165,
            fontFamily: $fontFamily,
            fontTextColor: $text,
            fontTitleColor: $text,
            buttonStyle: self::mapThemeButtonStyle((string) ($theme->button_style ?? 'solid')),
            buttonShape: self::mapThemeButtonShape((string) ($theme->button_style ?? 'pill')),
            buttonShadow: ($theme->button_style ?? '') === 'shadow' ? 'strong' : 'soft',
            buttonColor: $card,
            buttonTextColor: $accent,
        );
    }

    public static function wallpaperStyleForTheme(Theme $theme, Profile $profile): string
    {
        if ($profile->wallpaper_image_path) {
            return 'image';
        }

        return match ($theme->background_style) {
            'gradient', 'radial-glow' => 'gradient',
            default => 'solid',
        };
    }

    public static function fromProfile(Profile $profile): self
    {
        $vars = is_array($profile->theme_variables) ? $profile->theme_variables : [];
        $theme = $profile->theme;
        $themeVars = is_array($theme?->variables) ? $theme->variables : [];
        $fallback = app(BrandingService::class)->profileThemeFallbackVariables();

        $bg = (string) ($vars['bg'] ?? $themeVars['bg'] ?? $fallback['bg'] ?? '#f8fafc');
        $text = (string) ($vars['text'] ?? $themeVars['text'] ?? $fallback['text'] ?? '#0f172a');
        $card = (string) ($vars['card'] ?? $themeVars['card'] ?? $fallback['card'] ?? '#ffffff');

        $wallpaper = is_array($vars['wallpaper'] ?? null) ? $vars['wallpaper'] : [];
        $font = is_array($vars['font'] ?? null) ? $vars['font'] : [];
        $button = is_array($vars['button'] ?? null) ? $vars['button'] : [];

        $wallpaperStyle = (string) ($wallpaper['style'] ?? 'solid');
        if ($profile->wallpaper_image_path && $wallpaperStyle !== 'image') {
            $wallpaperStyle = 'image';
        }

        return new self(
            themeId: $profile->theme_id,
            headerLayout: (string) ($profile->header_layout ?: ($vars['header']['layout'] ?? 'classic')),
            wallpaperStyle: $wallpaperStyle,
            wallpaperColor: (string) ($wallpaper['color'] ?? $bg),
            wallpaperGradientFrom: (string) ($wallpaper['gradient_from'] ?? $bg),
            wallpaperGradientTo: (string) ($wallpaper['gradient_to'] ?? $themeVars['accent'] ?? '#6366f1'),
            wallpaperGradientAngle: (int) ($wallpaper['gradient_angle'] ?? 165),
            fontFamily: (string) ($font['family'] ?? $theme?->font_family ?? 'figtree'),
            fontTextColor: (string) ($font['text'] ?? $text),
            fontTitleColor: (string) ($font['title'] ?? $text),
            buttonStyle: self::mapThemeButtonStyle((string) ($button['style'] ?? $theme?->button_style ?? 'solid')),
            buttonShape: self::mapThemeButtonShape((string) ($button['shape'] ?? $theme?->button_style ?? 'pill')),
            buttonShadow: (string) ($button['shadow'] ?? ($theme?->button_style === 'shadow' ? 'strong' : 'soft')),
            buttonColor: (string) ($button['color'] ?? $card),
            buttonTextColor: (string) ($button['text_color'] ?? $text),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toThemeVariables(): array
    {
        $existing = [];

        return array_merge($existing, [
            'wallpaper' => [
                'style' => $this->wallpaperStyle,
                'color' => $this->wallpaperColor,
                'gradient_from' => $this->wallpaperGradientFrom,
                'gradient_to' => $this->wallpaperGradientTo,
                'gradient_angle' => $this->wallpaperGradientAngle,
            ],
            'header' => [
                'layout' => $this->headerLayout,
            ],
            'font' => [
                'family' => $this->fontFamily,
                'text' => $this->fontTextColor,
                'title' => $this->fontTitleColor,
            ],
            'button' => [
                'style' => $this->buttonStyle,
                'shape' => $this->buttonShape,
                'shadow' => $this->buttonShadow,
                'color' => $this->buttonColor,
                'text_color' => $this->buttonTextColor,
            ],
        ]);
    }

    public function applyToProfile(Profile $profile): void
    {
        $profile->theme_id = $this->themeId;
        $profile->header_layout = $this->headerLayout;
        $profile->theme_variables = $this->toThemeVariables();
    }

    public static function mapThemeButtonStyle(string $value): string
    {
        return match ($value) {
            'glass' => 'glass',
            'outline' => 'outline',
            'shadow' => 'solid',
            default => 'solid',
        };
    }

    public static function mapThemeButtonShape(string $value): string
    {
        return match ($value) {
            'square' => 'square',
            'rounded', 'glass', 'shadow' => 'rounded',
            default => 'pill',
        };
    }
}
