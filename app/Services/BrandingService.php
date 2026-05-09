<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BrandingService
{
    public const CACHE_KEY = 'branding.payload_v2';

    /** @var list<string> */
    protected const LEGAL_HTML_KEYS = [
        'legal.impressum_html',
        'legal.datenschutz_html',
        'legal.agb_html',
    ];

    /** @var list<string> */
    protected const JSON_LIST_KEYS = [
        'faq.items',
        'help.sections',
    ];

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

        foreach (self::LEGAL_HTML_KEYS as $dotKey) {
            $keys[] = 'branding.'.$dotKey;
        }

        $keys[] = 'branding.faq.items';
        $keys[] = 'branding.help.sections';
        $keys[] = 'branding.pricing.plans';

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
            'marketing.trust_count' => 'marketing.trust_count',
            'marketing.trust_count_label' => 'marketing.trust_count_label',
            'marketing.home_templates_title' => 'marketing.home_templates_title',
            'marketing.home_templates_subline' => 'marketing.home_templates_subline',
            'marketing.final_cta_title' => 'marketing.final_cta_title',
            'marketing.final_cta_subline' => 'marketing.final_cta_subline',
            'marketing.final_cta_button' => 'marketing.final_cta_button',
            'pricing.title' => 'pricing.title',
            'pricing.subline' => 'pricing.subline',
            'faq.title' => 'faq.title',
            'help.title' => 'help.title',
            'help.intro' => 'help.intro',
            'footer.legal_label' => 'footer.legal_label',
            'footer.nav_label' => 'footer.nav_label',
            'footer.brand_label' => 'footer.brand_label',
            'bio.cta_label_default' => 'bio.cta_label_default',
            'bio.platform_credit' => 'bio.platform_credit',
            'bio.platform_url_label' => 'bio.platform_url_label',
            'bio.cookie_text' => 'bio.cookie_text',
            'bio.cookie_button' => 'bio.cookie_button',
        ];

        foreach ([1, 2, 3] as $i) {
            $map["marketing.steps.{$i}.title"] = "marketing.steps.{$i}.title";
            $map["marketing.steps.{$i}.text"] = "marketing.steps.{$i}.text";
            $map["marketing.features.{$i}.title"] = "marketing.features.{$i}.title";
            $map["marketing.features.{$i}.text"] = "marketing.features.{$i}.text";
            $map["marketing.cards.{$i}.title"] = "marketing.cards.{$i}.title";
            $map["marketing.cards.{$i}.text"] = "marketing.cards.{$i}.text";
            $map["marketing.cards.{$i}.icon"] = "marketing.cards.{$i}.icon";
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
     * @return array<string, mixed>
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

        $translated = Lang::get('branding.'.$suffix);
        if (is_string($translated)) {
            return $translated;
        }

        return '';
    }

    /**
     * Rohinhalt für Legal/Markdown-Felder (HTML oder Markdown).
     */
    public function html(string $dotKey): string
    {
        $fullKey = 'branding.'.$dotKey;
        $stored = $this->settings->getStored($fullKey);
        if (is_string($stored) && $stored !== '') {
            return $stored;
        }

        $translated = Lang::get('branding.'.$dotKey);
        if (is_string($translated)) {
            return $translated;
        }

        return '';
    }

    /**
     * Dekodiert JSON-Arrays (FAQ, Hilfe-Sektionen).
     *
     * @return list<array<string, mixed>>
     */
    public function list(string $dotKey): array
    {
        $fullKey = 'branding.'.$dotKey;
        $stored = $this->settings->getStored($fullKey);
        if (is_string($stored) && $stored !== '') {
            $decoded = json_decode($stored, true);
            if (is_array($decoded)) {
                /** @var list<array<string, mixed>> $decoded */
                return array_values(array_filter($decoded, 'is_array'));
            }
        }

        $fallback = Lang::get('branding.'.$dotKey);
        if (is_array($fallback)) {
            /** @var list<array<string, mixed>> $fallback */
            return array_values(array_filter($fallback, 'is_array'));
        }

        return [];
    }

    /**
     * Abopläne (free, starter, pro) aus JSON oder aus Lang-Defaults.
     *
     * @return array<string, array{name: string, price: string, period: string, features: list<string>, cta: string}>
     */
    public function pricingPlans(): array
    {
        $stored = $this->settings->getStored('branding.pricing.plans');
        $defaults = Lang::get('branding.pricing.plans');
        if (! is_array($defaults)) {
            $defaults = [];
        }

        if (! is_string($stored) || $stored === '') {
            return $this->normalizePricingPlans($defaults);
        }

        $decoded = json_decode($stored, true);
        if (! is_array($decoded)) {
            return $this->normalizePricingPlans($defaults);
        }

        return $this->normalizePricingPlans(array_replace_recursive($defaults, $decoded));
    }

    /**
     * Rendert gespeicherten Markdown/HTML-Inhalt sicher für die Ausgabe.
     */
    public function renderRich(?string $raw): string
    {
        if ($raw === null || trim($raw) === '') {
            return '';
        }

        $trimmed = trim($raw);
        if (str_starts_with($trimmed, '<')) {
            return strip_tags($trimmed, '<p><br><br/><strong><b><em><i><u><ul><ol><li><a><h1><h2><h3><h4><blockquote><div><span><hr><code><pre>');
        }

        return Str::markdown($trimmed);
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

        return (string) Lang::get('branding.colors.'.$shortKey);
    }

    public function brandName(): string
    {
        $stored = $this->settings->getStored('branding.brand_name');
        if (is_string($stored) && $stored !== '') {
            return $stored;
        }

        return (string) Lang::get('branding.brand_name');
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
            $out[$k] = (string) Lang::get('branding.colors.'.$k);
        }

        return $out;
    }

    /**
     * @return array<string, string>
     */
    public function defaultTextsFlat(): array
    {
        $out = [
            'brand_name' => (string) Lang::get('branding.brand_name'),
        ];

        foreach (self::textKeyToSettingSuffix() as $suffix) {
            $val = Lang::get('branding.'.$suffix);
            $out[str_replace('.', '_', $suffix)] = is_string($val) ? $val : '';
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

        $cards = [];
        foreach ([1, 2, 3] as $i) {
            $cards[] = [
                'title' => $this->text("marketing.cards.{$i}.title"),
                'text' => $this->text("marketing.cards.{$i}.text"),
                'icon' => $this->text("marketing.cards.{$i}.icon"),
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
            'trust_count' => $this->text('marketing.trust_count'),
            'trust_count_label' => $this->text('marketing.trust_count_label'),
            'home_templates_title' => $this->text('marketing.home_templates_title'),
            'home_templates_subline' => $this->text('marketing.home_templates_subline'),
            'final_cta_title' => $this->text('marketing.final_cta_title'),
            'final_cta_subline' => $this->text('marketing.final_cta_subline'),
            'final_cta_button' => $this->text('marketing.final_cta_button'),
            'steps' => $steps,
            'features' => $features,
            'cards' => $cards,
        ];

        $bio = [
            'cta_label_default' => $this->text('bio.cta_label_default'),
            'platform_credit' => $this->text('bio.platform_credit'),
            'platform_url_label' => $this->text('bio.platform_url_label'),
            'cookie_text' => $this->text('bio.cookie_text'),
            'cookie_button' => $this->text('bio.cookie_button'),
        ];

        $footer = [
            'legal_label' => $this->text('footer.legal_label'),
            'nav_label' => $this->text('footer.nav_label'),
            'brand_label' => $this->text('footer.brand_label'),
        ];

        $faq = [
            'title' => $this->text('faq.title'),
            'items' => $this->list('faq.items'),
        ];

        $help = [
            'title' => $this->text('help.title'),
            'intro' => $this->text('help.intro'),
            'sections' => $this->list('help.sections'),
        ];

        $pricing = [
            'title' => $this->text('pricing.title'),
            'subline' => $this->text('pricing.subline'),
            'plans' => $this->pricingPlans(),
        ];

        $legal = [
            'impressum_html' => $this->html('legal.impressum_html'),
            'datenschutz_html' => $this->html('legal.datenschutz_html'),
            'agb_html' => $this->html('legal.agb_html'),
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
            'footer' => $footer,
            'faq' => $faq,
            'help' => $help,
            'pricing' => $pricing,
            'legal' => $legal,
        ];
    }

    /**
     * @param  array<string, mixed>  $raw
     * @return array<string, array{name: string, price: string, period: string, features: list<string>, cta: string}>
     */
    protected function normalizePricingPlans(array $raw): array
    {
        $langDefaults = Lang::get('branding.pricing.plans');
        $defaults = is_array($langDefaults) ? $langDefaults : [];

        $out = [];
        foreach (['free', 'starter', 'pro'] as $key) {
            $block = is_array($raw[$key] ?? null) ? $raw[$key] : [];
            $defBlock = is_array($defaults[$key] ?? null) ? $defaults[$key] : [];

            $features = $block['features'] ?? [];
            if (is_string($features)) {
                $features = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $features) ?: [])));
            }
            if (! is_array($features)) {
                $features = [];
            }
            /** @var list<string> $features */
            $features = array_values(array_filter(array_map(static fn ($f) => is_string($f) ? $f : '', $features)));
            if ($features === [] && isset($defBlock['features']) && is_array($defBlock['features'])) {
                $features = array_values(array_filter(array_map(static fn ($f) => is_string($f) ? $f : '', $defBlock['features'])));
            }

            $name = $block['name'] ?? null;
            if (! is_string($name) || trim($name) === '') {
                $name = is_string($defBlock['name'] ?? null) ? $defBlock['name'] : ucfirst($key);
            }

            $price = $block['price'] ?? null;
            if (! is_string($price) || trim($price) === '') {
                $price = is_string($defBlock['price'] ?? null) ? $defBlock['price'] : '';
            }

            $period = $block['period'] ?? null;
            if (! is_string($period) || trim($period) === '') {
                $period = is_string($defBlock['period'] ?? null) ? $defBlock['period'] : '';
            }

            $cta = $block['cta'] ?? null;
            if (! is_string($cta) || trim($cta) === '') {
                $cta = is_string($defBlock['cta'] ?? null) ? $defBlock['cta'] : '';
            }

            $out[$key] = [
                'name' => $name,
                'price' => $price,
                'period' => $period,
                'features' => $features,
                'cta' => $cta,
            ];
        }

        return $out;
    }
}
