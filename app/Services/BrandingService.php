<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class BrandingService
{
    public const CACHE_KEY = 'branding.payload_v1';

    public function __construct(
        protected SettingsService $settings
    ) {}

    /**
     * @return array<int, string>
     */
    public static function allSettingKeys(): array
    {
        $keys = [
            'branding.brand_name',
            'branding.brand_logo_path',
        ];

        foreach (self::colorShortKeys() as $k) {
            $keys[] = 'branding.colors.'.$k;
        }

        $textMap = self::textKeyToSettingSuffix();
        foreach (array_keys($textMap) as $dotKey) {
            $keys[] = 'branding.'.$dotKey;
        }

        return $keys;
    }

    /**
     * @return list<string>
     */
    public static function colorShortKeys(): array
    {
        return [
            'primary',
            'primary_contrast',
            'accent',
            'bg',
            'bg_alt',
            'text',
            'text_muted',
            'card',
            'border',
        ];
    }

    /**
     * Map dot-key (e.g. marketing.headline) → settings suffix branding.{suffix}
     *
     * @return array<string, string>
     */
    public static function textKeyToSettingSuffix(): array
    {
        $map = [
            'marketing.eyebrow' => 'marketing.eyebrow',
            'marketing.headline' => 'marketing.headline',
            'marketing.subline' => 'marketing.subline',
            'marketing.cta_primary' => 'marketing.cta_primary',
            'marketing.cta_secondary' => 'marketing.cta_secondary',
            'marketing.footer_tagline' => 'marketing.footer_tagline',
            'marketing.trust_strip' => 'marketing.trust_strip',
            'marketing.final_cta_title' => 'marketing.final_cta_title',
            'marketing.final_cta_subline' => 'marketing.final_cta_subline',
            'marketing.final_cta_button' => 'marketing.final_cta_button',
            'bio.cta_label_default' => 'bio.cta_label_default',
            'bio.platform_credit' => 'bio.platform_credit',
            'bio.platform_url_label' => 'bio.platform_url_label',
        ];

        foreach ([1, 2, 3] as $i) {
            $map["marketing.steps.{$i}.title"] = "marketing.steps.{$i}.title";
            $map["marketing.steps.{$i}.text"] = "marketing.steps.{$i}.text";
            $map["marketing.features.{$i}.title"] = "marketing.features.{$i}.title";
            $map["marketing.features.{$i}.text"] = "marketing.features.{$i}.text";
        }

        return $map;
    }

    public function flushPayloadCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Resolved branding payload (cached forever until flush).
     *
     * @return array{
     *     brand_name: string,
     *     brand_logo_path: ?string,
     *     brand_logo_url: ?string,
     *     colors: array<string, string>,
     *     marketing: array<string, mixed>,
     *     bio: array<string, string>
     * }
     */
    public function payload(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, fn (): array => $this->buildPayload());
    }

    public function text(string $dotKey, ?string $default = null): string
    {
        $suffix = self::textKeyToSettingSuffix()[$dotKey] ?? null;
        if ($suffix === null) {
            $suffix = $dotKey;
        }

        $fullKey = 'branding.'.$suffix;
        $stored = $this->settings->getStored($fullKey);
        if (is_string($stored) && $stored !== '') {
            return $stored;
        }

        if ($default !== null) {
            return $default;
        }

        return (string) __('branding.'.$suffix);
    }

    public function color(string $shortKey, ?string $default = null): string
    {
        if (! in_array($shortKey, self::colorShortKeys(), true)) {
            return $default ?? '#000000';
        }

        $fullKey = 'branding.colors.'.$shortKey;
        $stored = $this->settings->getStored($fullKey);
        if (is_string($stored) && $stored !== '') {
            return $stored;
        }

        if ($default !== null) {
            return $default;
        }

        return (string) __('branding.colors.'.$shortKey);
    }

    public function brandName(): string
    {
        $stored = $this->settings->getStored('branding.brand_name');
        if (is_string($stored) && $stored !== '') {
            return $stored;
        }

        return (string) __('branding.brand_name');
    }

    public function brandLogoUrl(): ?string
    {
        $path = $this->settings->getStored('branding.brand_logo_path');
        if (! is_string($path) || $path === '') {
            return null;
        }

        return Storage::disk('public')->url($path);
    }

    /**
     * CSS variables for marketing layout (--brand-*).
     *
     * @return array<string, string>
     */
    public function cssVariables(): array
    {
        $p = $this->payload();
        $c = $p['colors'];

        return [
            '--brand-primary' => $c['primary'],
            '--brand-primary-contrast' => $c['primary_contrast'],
            '--brand-accent' => $c['accent'],
            '--brand-bg' => $c['bg'],
            '--brand-bg-alt' => $c['bg_alt'],
            '--brand-text' => $c['text'],
            '--brand-text-muted' => $c['text_muted'],
            '--brand-card' => $c['card'],
            '--brand-border' => $c['border'],
        ];
    }

    /**
     * Theme-like variables for public bio page fallback (keys bg, text, accent, card, border).
     *
     * @return array<string, string>
     */
    public function profileThemeFallbackVariables(): array
    {
        $p = $this->payload();
        $c = $p['colors'];

        return [
            'bg' => $c['bg'],
            'text' => $c['text'],
            'accent' => $c['accent'],
            'card' => $c['card'],
            'border' => $c['border'],
            'text_muted' => $c['text_muted'],
            'primary_contrast' => $c['primary_contrast'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function defaultColors(): array
    {
        $out = [];
        foreach (self::colorShortKeys() as $k) {
            $out[$k] = (string) __('branding.colors.'.$k);
        }

        return $out;
    }

    /**
     * @return array<string, string>
     */
    public function defaultTextsFlat(): array
    {
        $out = [
            'brand_name' => (string) __('branding.brand_name'),
        ];

        foreach (self::textKeyToSettingSuffix() as $suffix) {
            $out[str_replace('.', '_', $suffix)] = (string) __('branding.'.$suffix);
        }

        return $out;
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildPayload(): array
    {
        $colors = [];
        foreach (self::colorShortKeys() as $k) {
            $colors[$k] = $this->color($k);
        }

        $steps = [];
        foreach ([1, 2, 3] as $i) {
            $steps[] = [
                'title' => $this->text("marketing.steps.{$i}.title"),
                'text' => $this->text("marketing.steps.{$i}.text"),
            ];
        }

        $features = [];
        foreach ([1, 2, 3] as $i) {
            $features[] = [
                'title' => $this->text("marketing.features.{$i}.title"),
                'text' => $this->text("marketing.features.{$i}.text"),
            ];
        }

        $marketing = [
            'eyebrow' => $this->text('marketing.eyebrow'),
            'headline' => $this->text('marketing.headline'),
            'subline' => $this->text('marketing.subline'),
            'cta_primary' => $this->text('marketing.cta_primary'),
            'cta_secondary' => $this->text('marketing.cta_secondary'),
            'footer_tagline' => $this->text('marketing.footer_tagline'),
            'trust_strip' => $this->text('marketing.trust_strip'),
            'final_cta_title' => $this->text('marketing.final_cta_title'),
            'final_cta_subline' => $this->text('marketing.final_cta_subline'),
            'final_cta_button' => $this->text('marketing.final_cta_button'),
            'steps' => $steps,
            'features' => $features,
        ];

        $bio = [
            'cta_label_default' => $this->text('bio.cta_label_default'),
            'platform_credit' => $this->text('bio.platform_credit'),
            'platform_url_label' => $this->text('bio.platform_url_label'),
        ];

        $logoPath = $this->settings->getStored('branding.brand_logo_path');
        $path = is_string($logoPath) && $logoPath !== '' ? $logoPath : null;

        return [
            'brand_name' => $this->brandName(),
            'brand_logo_path' => $path,
            'brand_logo_url' => $path !== null ? Storage::disk('public')->url($path) : null,
            'colors' => $colors,
            'marketing' => $marketing,
            'bio' => $bio,
        ];
    }
}
