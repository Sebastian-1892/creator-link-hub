@extends('layouts.marketing')

@section('title', __('Startseite'))

@section('content')
    @php
        $m = $branding['marketing'];
        $previewThemes = $previewThemes ?? collect();
    @endphp

    {{-- Hero --}}
    <section class="max-w-6xl mx-auto px-4 pt-16 pb-12 lg:pt-24 lg:pb-20">
        <div class="grid gap-12 lg:grid-cols-2 lg:items-center">
            <div>
                <p class="text-sm font-semibold uppercase tracking-widest" style="color: var(--brand-accent);">{{ $m['eyebrow'] }}</p>
                <h1 class="mt-4 text-4xl sm:text-5xl lg:text-6xl font-bold leading-tight" style="color: var(--brand-text);">
                    {{ $m['headline'] }}
                </h1>
                <p class="mt-6 text-lg leading-relaxed max-w-xl" style="color: var(--brand-text-muted);">
                    {{ $m['subline'] }}
                </p>
                <div class="mt-10 flex flex-col sm:flex-row gap-4">
                    <a
                        href="{{ route('register') }}"
                        class="inline-flex justify-center rounded-full px-8 py-3.5 text-lg font-semibold shadow-xl transition hover:-translate-y-0.5 hover:shadow-2xl"
                        style="background: var(--brand-primary); color: var(--brand-primary-contrast);"
                    >{{ $m['cta_primary'] }}</a>
                    <a
                        href="{{ route('pricing') }}"
                        class="inline-flex justify-center rounded-full border px-8 py-3.5 text-lg font-semibold transition hover:-translate-y-0.5"
                        style="border-color: var(--brand-border); color: var(--brand-text);"
                    >{{ $m['cta_secondary'] }}</a>
                </div>
            </div>
            <div class="relative mx-auto w-full max-w-sm">
                <div class="absolute -inset-4 rounded-[2rem] blur-3xl opacity-60" style="background: radial-gradient(circle, var(--brand-accent) 0%, transparent 65%);"></div>
                <div
                    class="relative rounded-[2rem] border p-6 shadow-2xl"
                    style="border-color: var(--brand-border); background: var(--brand-card);"
                >
                    <div class="flex items-center gap-3">
                        <div class="h-14 w-14 rounded-full border-2 shrink-0" style="border-color: var(--brand-accent); background: color-mix(in srgb, var(--brand-accent) 22%, transparent);"></div>
                        <div class="min-w-0 flex-1 space-y-2">
                            <div class="h-3 rounded-full opacity-80 w-3/4" style="background: var(--brand-text);"></div>
                            <div class="h-2 rounded-full opacity-40 w-full" style="background: var(--brand-text-muted);"></div>
                        </div>
                    </div>
                    <div class="mt-6 space-y-3">
                        @foreach (['Shop', 'Newsletter', 'Neuestes Video'] as $label)
                            <div
                                class="flex items-center justify-between rounded-2xl px-4 py-3.5 text-sm font-semibold shadow-md transition hover:-translate-y-0.5"
                                style="background: color-mix(in srgb, var(--brand-bg-alt) 65%, transparent); border: 1px solid var(--brand-border); color: var(--brand-primary);"
                            >
                                <span>{{ $label }}</span>
                                <span class="opacity-0 group-hover:opacity-100 text-xs" aria-hidden="true">→</span>
                            </div>
                        @endforeach
                    </div>
                    <p class="mt-6 text-center text-xs" style="color: var(--brand-text-muted);">{{ $branding['bio']['platform_credit'] }} {{ $branding['brand_name'] }}</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Steps --}}
    <section class="max-w-6xl mx-auto px-4 py-16">
        <h2 class="text-center text-2xl sm:text-3xl font-bold" style="color: var(--brand-text);">{{ __('So funktioniert es') }}</h2>
        <div class="mt-12 grid gap-6 md:grid-cols-3">
            @foreach ($m['steps'] as $idx => $step)
                <div
                    class="rounded-2xl border p-6 transition hover:-translate-y-1 hover:shadow-xl"
                    style="border-color: var(--brand-border); background: var(--brand-card);"
                >
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-xl text-lg font-bold"
                        style="background: color-mix(in srgb, var(--brand-primary) 25%, transparent); color: var(--brand-primary);"
                    >{{ $idx + 1 }}</div>
                    <h3 class="mt-4 text-lg font-semibold" style="color: var(--brand-text);">{{ $step['title'] }}</h3>
                    <p class="mt-2 text-sm leading-relaxed" style="color: var(--brand-text-muted);">{{ $step['text'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Features --}}
    <section class="max-w-6xl mx-auto px-4 py-12">
        <h2 class="text-center text-2xl sm:text-3xl font-bold" style="color: var(--brand-text);">{{ __('Warum :name?', ['name' => $branding['brand_name']]) }}</h2>
        <div class="mt-12 grid gap-6 md:grid-cols-3">
            @foreach ($m['features'] as $feature)
                <div class="rounded-2xl border p-6" style="border-color: var(--brand-border); background: color-mix(in srgb, var(--brand-card) 80%, transparent);">
                    <div class="h-10 w-10 rounded-lg" style="background: linear-gradient(135deg, var(--brand-primary), var(--brand-accent)); opacity: 0.9;"></div>
                    <h3 class="mt-4 font-semibold text-lg" style="color: var(--brand-text);">{{ $feature['title'] }}</h3>
                    <p class="mt-2 text-sm leading-relaxed" style="color: var(--brand-text-muted);">{{ $feature['text'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Trust strip --}}
    <section class="border-y py-10" style="border-color: var(--brand-border); background: color-mix(in srgb, var(--brand-card) 40%, transparent);">
        <div class="max-w-6xl mx-auto px-4 text-center">
            <p class="text-sm font-medium uppercase tracking-wider" style="color: var(--brand-text-muted);">{{ $m['trust_strip'] }}</p>
            <div class="mt-8 flex flex-wrap justify-center gap-8 opacity-70">
                @foreach (range(1, 5) as $i)
                    <div class="h-8 w-24 rounded-md" style="background: var(--brand-border);"></div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Templates / Themes --}}
    <section class="max-w-6xl mx-auto px-4 py-20">
        <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-6">
            <div>
                <h2 class="text-2xl sm:text-3xl font-bold" style="color: var(--brand-text);">{{ __('Beliebte Vorlagen') }}</h2>
                <p class="mt-2 max-w-xl text-sm" style="color: var(--brand-text-muted);">{{ __('Drei Beispiel-Themes aus dem Hub — so kann deine Bio-Seite wirken.') }}</p>
            </div>
            <a href="{{ route('register') }}" class="text-sm font-semibold underline-offset-4 hover:underline shrink-0" style="color: var(--brand-accent);">{{ __('Alle Themes im Dashboard') }} →</a>
        </div>
        <div class="mt-10 grid gap-6 sm:grid-cols-3">
            @foreach ($previewThemes as $theme)
                @php
                    $v = is_array($theme->variables) ? $theme->variables : [];
                    $bg = $v['bg'] ?? '#1e293b';
                    $text = $v['text'] ?? '#f8fafc';
                    $accent = $v['accent'] ?? '#22d3ee';
                    $card = $v['card'] ?? '#334155';
                @endphp
                <div class="rounded-2xl border overflow-hidden shadow-lg" style="border-color: var(--brand-border);">
                    <div class="px-4 py-3 text-xs font-semibold flex justify-between" style="background: {{ $bg }}; color: {{ $text }};">
                        <span>{{ $theme->name }}</span>
                        <span style="color: {{ $accent }};">●</span>
                    </div>
                    <div class="p-4 space-y-2" style="background: {{ $bg }};">
                        <div class="mx-auto h-12 w-12 rounded-full border-2" style="border-color: {{ $accent }};"></div>
                        <div class="h-2 rounded-full mx-auto w-1/2 opacity-50" style="background: {{ $text }};"></div>
                        <div class="h-9 rounded-xl mt-3 text-center text-xs font-semibold leading-9" style="background: {{ $card }}; color: {{ $accent }};">Link</div>
                        <div class="h-9 rounded-xl text-center text-xs font-semibold leading-9" style="background: {{ $card }}; color: {{ $accent }};">Link</div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Final CTA --}}
    <section class="max-w-6xl mx-auto px-4 pb-24">
        <div
            class="rounded-[2rem] border px-8 py-14 text-center shadow-2xl"
            style="border-color: var(--brand-border); background: linear-gradient(135deg, color-mix(in srgb, var(--brand-primary) 35%, var(--brand-bg)) 0%, var(--brand-bg-alt) 100%);"
        >
            <h2 class="text-3xl sm:text-4xl font-bold" style="color: var(--brand-text);">{{ $m['final_cta_title'] }}</h2>
            <p class="mt-4 max-w-2xl mx-auto text-lg" style="color: var(--brand-text-muted);">{{ $m['final_cta_subline'] }}</p>
            <a
                href="{{ route('register') }}"
                class="mt-10 inline-flex rounded-full px-10 py-4 text-lg font-semibold shadow-xl transition hover:-translate-y-0.5"
                style="background: var(--brand-primary); color: var(--brand-primary-contrast);"
            >{{ $m['final_cta_button'] }}</a>
        </div>
    </section>
@endsection
