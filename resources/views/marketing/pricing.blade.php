@extends('layouts.marketing')

@section('title', __('Preise'))

@section('content')
    <div class="max-w-5xl mx-auto px-4 py-16 lg:py-20">
        <div class="text-center max-w-2xl mx-auto">
            <p class="text-sm font-semibold uppercase tracking-wider" style="color: var(--brand-accent);">{{ __('Preise') }}</p>
            <h1 class="mt-3 text-3xl sm:text-4xl font-bold" style="color: var(--brand-text);">{{ __('Starte kostenlos und upgrade, wenn du mehr brauchst.') }}</h1>
        </div>
        <div class="mt-14 grid gap-6 md:grid-cols-3">
            <div
                class="rounded-2xl border p-7 flex flex-col transition hover:-translate-y-1 hover:shadow-xl"
                style="border-color: var(--brand-border); background: var(--brand-card);"
            >
                <h2 class="text-lg font-semibold" style="color: var(--brand-text);">Free</h2>
                <p class="mt-3 text-3xl font-bold" style="color: var(--brand-text);">0 €</p>
                <ul class="mt-6 space-y-2 text-sm flex-1" style="color: var(--brand-text-muted);">
                    <li>• {{ __('1 Profil') }}</li>
                    <li>• {{ __('Bis zu 10 Links') }}</li>
                    <li>• {{ __('Basis-Analytics') }}</li>
                    <li>• {{ __('Plattform-Branding') }}</li>
                </ul>
                <a
                    href="{{ route('register') }}"
                    class="mt-8 block text-center rounded-full border py-2.5 text-sm font-semibold transition hover:bg-white/5"
                    style="border-color: var(--brand-border); color: var(--brand-text);"
                >{{ __('Starten') }}</a>
            </div>
            <div
                class="rounded-2xl border-2 p-7 flex flex-col shadow-xl transition hover:-translate-y-1 md:scale-[1.02]"
                style="border-color: var(--brand-primary); background: color-mix(in srgb, var(--brand-card) 92%, var(--brand-primary));"
            >
                <h2 class="text-lg font-semibold" style="color: var(--brand-accent);">Starter</h2>
                <p class="mt-3 text-3xl font-bold" style="color: var(--brand-text);">9 € <span class="text-base font-normal" style="color: var(--brand-text-muted);">/ {{ __('Monat') }}</span></p>
                <ul class="mt-6 space-y-2 text-sm flex-1" style="color: var(--brand-text);">
                    <li>• {{ __('Unbegrenzte Links') }}</li>
                    <li>• {{ __('Ohne Plattform-Branding') }}</li>
                    <li>• {{ __('Eigene Domain (Roadmap)') }}</li>
                </ul>
                <a
                    href="{{ route('register') }}"
                    class="mt-8 block text-center rounded-full py-2.5 text-sm font-semibold transition hover:opacity-95"
                    style="background: var(--brand-primary); color: var(--brand-primary-contrast);"
                >{{ __('Upgrade im Dashboard') }}</a>
            </div>
            <div
                class="rounded-2xl border p-7 flex flex-col transition hover:-translate-y-1 hover:shadow-xl"
                style="border-color: var(--brand-border); background: var(--brand-card);"
            >
                <h2 class="text-lg font-semibold" style="color: var(--brand-text);">Pro</h2>
                <p class="mt-3 text-3xl font-bold" style="color: var(--brand-text);">24 € <span class="text-base font-normal" style="color: var(--brand-text-muted);">/ {{ __('Monat') }}</span></p>
                <ul class="mt-6 space-y-2 text-sm flex-1" style="color: var(--brand-text-muted);">
                    <li>• {{ __('Alles aus Starter') }}</li>
                    <li>• {{ __('Referrals & Rewards (Roadmap)') }}</li>
                    <li>• {{ __('UTM & Conversion (Roadmap)') }}</li>
                </ul>
                <a
                    href="{{ route('register') }}"
                    class="mt-8 block text-center rounded-full border py-2.5 text-sm font-semibold transition hover:bg-white/5"
                    style="border-color: var(--brand-border); color: var(--brand-text);"
                >{{ __('Upgrade im Dashboard') }}</a>
            </div>
        </div>
    </div>
@endsection
