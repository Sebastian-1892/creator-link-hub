<?php

namespace App\Support;

use Illuminate\Support\Facades\Lang;

class PageSectionContentBuilder
{
    /**
     * @return array<string, string>
     */
    public function buildHomeSections(string $locale, string $brandName, string $registerUrl, string $pricingUrl): array
    {
        $m = Lang::get('branding.marketing', [], $locale);
        if (! is_array($m)) {
            $m = [];
        }

        $cards = is_array($m['cards'] ?? null) ? $m['cards'] : [];
        $features = is_array($m['features'] ?? null) ? $m['features'] : [];
        $featuresHeading = str_replace(':name', e($brandName), (string) ($m['features_heading'] ?? ''));

        return [
            'hero' => $this->hero($m, $registerUrl, $pricingUrl),
            'trust' => $this->trust($m),
            'cards' => $this->cards($cards),
            'features' => $this->features($features, $featuresHeading),
            'templates_intro' => $this->templatesIntro($m, $registerUrl),
            'final_cta' => $this->finalCta($m, $registerUrl),
        ];
    }

    /**
     * @param  array<string, mixed>  $m
     */
    private function hero(array $m, string $registerUrl, string $pricingUrl): string
    {
        $eyebrow = e((string) ($m['eyebrow'] ?? ''));
        $headline = e((string) ($m['headline'] ?? ''));
        $subline = e((string) ($m['subline'] ?? ''));
        $ctaPrimary = e((string) ($m['cta_primary'] ?? ''));
        $ctaSecondary = e((string) ($m['cta_secondary'] ?? ''));

        return <<<HTML
<section class="max-w-4xl mx-auto px-4 pt-16 pb-10 lg:pt-24 lg:pb-14 text-center">
    <p class="text-sm font-semibold uppercase tracking-widest text-[color:var(--brand-accent)]">{$eyebrow}</p>
    <h1 class="mt-5 text-5xl sm:text-6xl lg:text-7xl font-extrabold tracking-tight leading-[1.08] w-full text-[color:var(--brand-text)]">{$headline}</h1>
    <p class="mt-8 text-lg sm:text-xl leading-relaxed max-w-2xl mx-auto w-full text-[color:var(--brand-text-muted)]">{$subline}</p>
    <div class="mt-12 flex flex-col sm:flex-row gap-4 justify-center">
        <a href="{$registerUrl}" class="inline-flex justify-center rounded-full px-10 py-4 text-lg font-semibold shadow-lg transition hover:-translate-y-0.5 hover:shadow-xl bg-[color:var(--brand-primary)] text-[color:var(--brand-primary-contrast)]">{$ctaPrimary}</a>
        <a href="{$pricingUrl}" class="inline-flex justify-center rounded-full border-2 px-10 py-4 text-lg font-semibold transition hover:-translate-y-0.5 bg-white border-[color:var(--brand-border)] text-[color:var(--brand-text)]">{$ctaSecondary}</a>
    </div>
</section>
HTML;
    }

    /**
     * @param  array<string, mixed>  $m
     */
    private function trust(array $m): string
    {
        $strip = e((string) ($m['trust_strip'] ?? ''));
        $count = e((string) ($m['trust_count'] ?? ''));
        $label = e((string) ($m['trust_count_label'] ?? ''));

        return <<<HTML
<section class="border-y py-12 border-[color:var(--brand-border)] bg-[color:var(--brand-card)]">
    <div class="max-w-6xl mx-auto px-4 text-center">
        <p class="text-xs font-semibold uppercase tracking-wider w-full text-[color:var(--brand-text-muted)]">{$strip}</p>
        <p class="mt-5 text-5xl sm:text-6xl font-black tabular-nums w-full text-[color:var(--brand-primary)]">{$count}</p>
        <p class="mt-3 text-base max-w-xl mx-auto w-full text-[color:var(--brand-text-muted)]">{$label}</p>
    </div>
</section>
HTML;
    }

    /**
     * @param  array<int|string, array<string, mixed>>  $cards
     */
    private function cards(array $cards): string
    {
        $items = '';
        foreach (array_values($cards) as $card) {
            if (! is_array($card)) {
                continue;
            }
            $icon = e((string) ($card['icon'] ?? ''));
            $title = e((string) ($card['title'] ?? ''));
            $text = e((string) ($card['text'] ?? ''));
            $items .= <<<HTML
        <div class="rounded-2xl border p-8 text-center shadow-sm transition hover:-translate-y-1 hover:shadow-lg bg-white border-[color:var(--brand-border)]">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full text-2xl font-semibold bg-[color:color-mix(in_srgb,var(--brand-accent)_18%,transparent)] text-[color:var(--brand-accent)]">
                <span>{$icon}</span>
            </div>
            <h3 class="mt-6 text-xl font-bold w-full text-[color:var(--brand-text)]">{$title}</h3>
            <p class="mt-3 text-sm leading-relaxed w-full text-[color:var(--brand-text-muted)]">{$text}</p>
        </div>
HTML;
        }

        return <<<HTML
<section class="max-w-6xl mx-auto px-4 py-20">
    <div class="grid gap-8 md:grid-cols-3">
{$items}
    </div>
</section>
HTML;
    }

    /**
     * @param  array<int|string, array<string, mixed>>  $features
     */
    private function features(array $features, string $featuresHeading): string
    {
        $items = '';
        foreach (array_values($features) as $feature) {
            if (! is_array($feature)) {
                continue;
            }
            $title = e((string) ($feature['title'] ?? ''));
            $text = e((string) ($feature['text'] ?? ''));
            $items .= <<<HTML
        <div class="rounded-2xl border p-6 bg-white shadow-sm border-[color:var(--brand-border)]">
            <div class="h-10 w-10 rounded-full bg-gradient-to-br from-[color:var(--brand-primary)] to-[color:var(--brand-accent)] opacity-85"></div>
            <h3 class="mt-4 font-semibold text-lg w-full text-[color:var(--brand-text)]">{$title}</h3>
            <p class="mt-2 text-sm leading-relaxed w-full text-[color:var(--brand-text-muted)]">{$text}</p>
        </div>
HTML;
        }

        return <<<HTML
<section class="max-w-6xl mx-auto px-4 py-12">
    <h2 class="text-center text-2xl sm:text-3xl font-bold w-full text-[color:var(--brand-text)]">{$featuresHeading}</h2>
    <div class="mt-12 grid gap-6 md:grid-cols-3">
{$items}
    </div>
</section>
HTML;
    }

    /**
     * @param  array<string, mixed>  $m
     */
    private function templatesIntro(array $m, string $registerUrl): string
    {
        $title = e((string) ($m['home_templates_title'] ?? ''));
        $subline = e((string) ($m['home_templates_subline'] ?? ''));

        return <<<HTML
<section class="pt-16 pb-0 overflow-hidden bg-[color:var(--brand-bg-alt)]">
    <div class="max-w-6xl mx-auto px-4">
        <div class="text-center max-w-2xl mx-auto">
            <h2 class="text-2xl sm:text-3xl font-bold w-full text-[color:var(--brand-text)]">{$title}</h2>
            <p class="mt-3 text-sm sm:text-base w-full text-[color:var(--brand-text-muted)]">{$subline}</p>
        </div>
    </div>
</section>
HTML;
    }

    /**
     * @param  array<string, mixed>  $m
     */
    private function finalCta(array $m, string $registerUrl): string
    {
        $title = e((string) ($m['final_cta_title'] ?? ''));
        $subline = e((string) ($m['final_cta_subline'] ?? ''));
        $button = e((string) ($m['final_cta_button'] ?? ''));

        return <<<HTML
<section class="max-w-6xl mx-auto px-4 pb-24 pt-6">
    <div class="rounded-[2rem] border px-8 py-14 text-center shadow-lg border-[color:var(--brand-border)] bg-[color:color-mix(in_srgb,var(--brand-bg-alt)_92%,var(--brand-primary))]">
        <h2 class="text-3xl sm:text-4xl font-bold w-full text-[color:var(--brand-text)]">{$title}</h2>
        <p class="mt-4 max-w-2xl mx-auto text-lg w-full text-[color:var(--brand-text-muted)]">{$subline}</p>
        <a href="{$registerUrl}" class="mt-10 inline-flex rounded-full px-10 py-4 text-lg font-semibold shadow-lg transition hover:-translate-y-0.5 bg-[color:var(--brand-primary)] text-[color:var(--brand-primary-contrast)]">{$button}</a>
    </div>
</section>
HTML;
    }
}
