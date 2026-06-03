@extends('layouts.marketing')

@section('title', __('Startseite'))

@section('content')
    @php
        $m = $branding['marketing'];
        $stripThemes = $stripThemes ?? collect();
        $carouselThemes = $carouselThemes ?? collect();
    @endphp

    {{-- Hero --}}
    <section class="max-w-4xl mx-auto px-4 pt-16 pb-10 lg:pt-24 lg:pb-14 text-center">
        <x-editable-translation
            translation-key="marketing.eyebrow"
            :value="$m['eyebrow']"
            tag="p"
            class="text-sm font-semibold uppercase tracking-widest"
            style="color: var(--brand-accent);"
        />
        <x-editable-translation
            translation-key="marketing.headline"
            :value="$m['headline']"
            tag="h1"
            class="mt-5 text-5xl sm:text-6xl lg:text-7xl font-extrabold tracking-tight leading-[1.08] block w-full"
            style="color: var(--brand-text);"
        />
        <x-editable-translation
            translation-key="marketing.subline"
            :value="$m['subline']"
            tag="p"
            class="mt-8 text-lg sm:text-xl leading-relaxed max-w-2xl mx-auto block w-full"
            style="color: var(--brand-text-muted);"
        />
        <div class="mt-12 flex flex-col sm:flex-row gap-4 justify-center">
            <a
                href="{{ route('register') }}"
                class="inline-flex justify-center rounded-full px-10 py-4 text-lg font-semibold shadow-lg transition hover:-translate-y-0.5 hover:shadow-xl"
                style="background: var(--brand-primary); color: var(--brand-primary-contrast);"
            >
                <x-editable-translation translation-key="marketing.cta_primary" :value="$m['cta_primary']" tag="span" />
            </a>
            <a
                href="{{ route('pricing') }}"
                class="inline-flex justify-center rounded-full border-2 px-10 py-4 text-lg font-semibold transition hover:-translate-y-0.5 bg-white"
                style="border-color: var(--brand-border); color: var(--brand-text);"
            >
                <x-editable-translation translation-key="marketing.cta_secondary" :value="$m['cta_secondary']" tag="span" />
            </a>
        </div>
    </section>

    {{-- Trust --}}
    <section class="border-y py-12" style="border-color: var(--brand-border); background: var(--brand-card);">
        <div class="max-w-6xl mx-auto px-4 text-center">
            <x-editable-translation
                translation-key="marketing.trust_strip"
                :value="$m['trust_strip']"
                tag="p"
                class="text-xs font-semibold uppercase tracking-wider block w-full"
                style="color: var(--brand-text-muted);"
            />
            <x-editable-translation
                translation-key="marketing.trust_count"
                :value="$m['trust_count']"
                tag="p"
                class="mt-5 text-5xl sm:text-6xl font-black tabular-nums block w-full"
                style="color: var(--brand-primary);"
            />
            <x-editable-translation
                translation-key="marketing.trust_count_label"
                :value="$m['trust_count_label']"
                tag="p"
                class="mt-3 text-base max-w-xl mx-auto block w-full"
                style="color: var(--brand-text-muted);"
            />
        </div>
    </section>

    {{-- 3 Icon cards --}}
    <section class="max-w-6xl mx-auto px-4 py-20">
        <div class="grid gap-8 md:grid-cols-3">
            @foreach ($m['cards'] as $cardIndex => $card)
                @php $cardKey = $cardIndex + 1; @endphp
                <div
                    class="rounded-2xl border p-8 text-center shadow-sm transition hover:-translate-y-1 hover:shadow-lg bg-white"
                    style="border-color: var(--brand-border);"
                >
                    <div
                        class="mx-auto flex h-16 w-16 items-center justify-center rounded-full text-2xl font-semibold"
                        style="background: color-mix(in srgb, var(--brand-accent) 18%, transparent); color: var(--brand-accent);"
                    >
                        <x-editable-translation
                            :translation-key="'marketing.cards.'.$cardKey.'.icon'"
                            :value="$card['icon']"
                            tag="span"
                        />
                    </div>
                    <x-editable-translation
                        :translation-key="'marketing.cards.'.$cardKey.'.title'"
                        :value="$card['title']"
                        tag="h3"
                        class="mt-6 text-xl font-bold block w-full"
                        style="color: var(--brand-text);"
                    />
                    <x-editable-translation
                        :translation-key="'marketing.cards.'.$cardKey.'.text'"
                        :value="$card['text']"
                        tag="p"
                        class="mt-3 text-sm leading-relaxed block w-full"
                        style="color: var(--brand-text-muted);"
                    />
                </div>
            @endforeach
        </div>
    </section>

    {{-- Mockup strip --}}
    <section class="max-w-6xl mx-auto px-4 pb-12">
        <p class="text-center text-sm font-semibold uppercase tracking-wider" style="color: var(--brand-text-muted);">{{ __('Live-Vorschau') }}</p>
        <div class="mt-8 grid gap-6 sm:grid-cols-3">
            @foreach ($stripThemes as $theme)
                @php
                    $v = is_array($theme->variables) ? $theme->variables : [];
                    $bg = $v['bg'] ?? '#1e293b';
                    $text = $v['text'] ?? '#f8fafc';
                    $accent = $v['accent'] ?? '#22d3ee';
                    $card = $v['card'] ?? '#334155';
                    $btnRadius = match ($theme->button_style ?? 'pill') {
                        'square' => '6px',
                        'rounded', 'glass', 'shadow' => '14px',
                        default => '9999px',
                    };
                @endphp
                <div class="rounded-2xl border overflow-hidden shadow-md bg-white" style="border-color: var(--brand-border);">
                    <div class="px-4 py-3 text-xs font-semibold flex justify-between" style="background: {{ $bg }}; color: {{ $text }};">
                        <span>{{ $theme->name }}</span>
                        <span style="color: {{ $accent }};">●</span>
                    </div>
                    <div class="p-4 space-y-2" style="background: {{ $bg }};">
                        <div class="mx-auto h-11 w-11 rounded-full border-2" style="border-color: {{ $accent }};"></div>
                        <div class="h-2 rounded-full mx-auto w-1/2 opacity-50" style="background: {{ $text }};"></div>
                        @foreach (range(1, 3) as $i)
                            <div
                                class="h-9 text-center text-xs font-semibold leading-9 px-2 truncate"
                                style="background: {{ $card }}; color: {{ $accent }}; border-radius: {{ $btnRadius }};"
                            >Link {{ $i }}</div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Features --}}
    <section class="max-w-6xl mx-auto px-4 py-12">
        @php
            $featuresHeading = str_replace(':name', $branding['brand_name'], $m['features_heading']);
        @endphp
        <x-editable-translation
            translation-key="marketing.features_heading"
            :value="$featuresHeading"
            tag="h2"
            class="text-center text-2xl sm:text-3xl font-bold block w-full"
            style="color: var(--brand-text);"
        />
        <div class="mt-12 grid gap-6 md:grid-cols-3">
            @foreach ($m['features'] as $featureIndex => $feature)
                @php $featureKey = $featureIndex + 1; @endphp
                <div class="rounded-2xl border p-6 bg-white shadow-sm" style="border-color: var(--brand-border);">
                    <div class="h-10 w-10 rounded-full" style="background: linear-gradient(135deg, var(--brand-primary), var(--brand-accent)); opacity: 0.85;"></div>
                    <x-editable-translation
                        :translation-key="'marketing.features.'.$featureKey.'.title'"
                        :value="$feature['title']"
                        tag="h3"
                        class="mt-4 font-semibold text-lg block w-full"
                        style="color: var(--brand-text);"
                    />
                    <x-editable-translation
                        :translation-key="'marketing.features.'.$featureKey.'.text'"
                        :value="$feature['text']"
                        tag="p"
                        class="mt-2 text-sm leading-relaxed block w-full"
                        style="color: var(--brand-text-muted);"
                    />
                </div>
            @endforeach
        </div>
    </section>

    {{-- Free templates carousel --}}
    <section class="py-16 overflow-hidden" style="background: var(--brand-bg-alt);">
        <div class="max-w-6xl mx-auto px-4">
            <div class="text-center max-w-2xl mx-auto">
                <x-editable-translation
                    translation-key="marketing.home_templates_title"
                    :value="$m['home_templates_title']"
                    tag="h2"
                    class="text-2xl sm:text-3xl font-bold block w-full"
                    style="color: var(--brand-text);"
                />
                <x-editable-translation
                    translation-key="marketing.home_templates_subline"
                    :value="$m['home_templates_subline']"
                    tag="p"
                    class="mt-3 text-sm sm:text-base block w-full"
                    style="color: var(--brand-text-muted);"
                />
            </div>
            <div class="mt-10 flex gap-5 overflow-x-auto pb-4 snap-x snap-mandatory scrollbar-thin">
                @foreach ($carouselThemes as $theme)
                    @php
                        $v = is_array($theme->variables) ? $theme->variables : [];
                        $bg = $v['bg'] ?? '#f8fafc';
                        $text = $v['text'] ?? '#0f172a';
                        $accent = $v['accent'] ?? '#2563eb';
                        $card = $v['card'] ?? '#ffffff';
                    @endphp
                    <div class="min-w-[220px] snap-start rounded-2xl border shadow-md bg-white overflow-hidden" style="border-color: var(--brand-border);">
                        <div class="px-3 py-2 text-[11px] font-semibold truncate" style="background: {{ $bg }}; color: {{ $text }};">{{ $theme->name }}</div>
                        <div class="p-3 space-y-2" style="background: {{ $bg }};">
                            <div class="mx-auto h-8 w-8 rounded-full border-2 shrink-0" style="border-color: {{ $accent }};"></div>
                            @foreach (range(1, 3) as $i)
                                <div class="h-7 rounded-lg text-[10px] font-semibold leading-7 text-center truncate px-1" style="background: {{ $card }}; color: {{ $accent }};">Btn {{ $i }}</div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="text-center mt-6">
                <a href="{{ route('register') }}" class="text-sm font-semibold underline-offset-4 hover:underline" style="color: var(--brand-accent);">
                    <x-editable-translation translation-key="marketing.all_templates_link" :value="$m['all_templates_link']" tag="span" /> →
                </a>
            </div>
        </div>
    </section>

    {{-- Final CTA --}}
    <section class="max-w-6xl mx-auto px-4 pb-24 pt-6">
        <div
            class="rounded-[2rem] border px-8 py-14 text-center shadow-lg"
            style="border-color: var(--brand-border); background: color-mix(in srgb, var(--brand-bg-alt) 92%, var(--brand-primary));"
        >
            <x-editable-translation
                translation-key="marketing.final_cta_title"
                :value="$m['final_cta_title']"
                tag="h2"
                class="text-3xl sm:text-4xl font-bold block w-full"
                style="color: var(--brand-text);"
            />
            <x-editable-translation
                translation-key="marketing.final_cta_subline"
                :value="$m['final_cta_subline']"
                tag="p"
                class="mt-4 max-w-2xl mx-auto text-lg block w-full"
                style="color: var(--brand-text-muted);"
            />
            <a
                href="{{ route('register') }}"
                class="mt-10 inline-flex rounded-full px-10 py-4 text-lg font-semibold shadow-lg transition hover:-translate-y-0.5"
                style="background: var(--brand-primary); color: var(--brand-primary-contrast);"
            >
                <x-editable-translation translation-key="marketing.final_cta_button" :value="$m['final_cta_button']" tag="span" />
            </a>
        </div>
    </section>
@endsection
