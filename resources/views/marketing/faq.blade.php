@extends('layouts.marketing')

@section('title', 'FAQ')

@section('content')
    <div class="max-w-3xl mx-auto px-4 py-16 lg:py-20">
        <div class="text-center mb-12">
            <p class="text-sm font-semibold uppercase tracking-wider" style="color: var(--brand-accent);">FAQ</p>
            <h1 class="mt-3 text-3xl sm:text-4xl font-bold" style="color: var(--brand-text);">{{ __('Häufige Fragen') }}</h1>
        </div>
        <div class="space-y-6">
            <div class="rounded-2xl border p-6" style="border-color: var(--brand-border); background: var(--brand-card);">
                <h2 class="font-semibold text-lg" style="color: var(--brand-text);">{{ __('Wie funktioniert Click-Tracking?') }}</h2>
                <p class="mt-3 text-sm leading-relaxed" style="color: var(--brand-text-muted);">{{ __('Besucher klicken auf einen Smart-Link, der über /go/{id} läuft. Wir zählen den Klick (ohne Klartext-IP) und leiten weiter.') }}</p>
            </div>
            <div class="rounded-2xl border p-6" style="border-color: var(--brand-border); background: var(--brand-card);">
                <h2 class="font-semibold text-lg" style="color: var(--brand-text);">{{ __('Kann ich später upgraden?') }}</h2>
                <p class="mt-3 text-sm leading-relaxed" style="color: var(--brand-text-muted);">{{ __('Ja — Abrechnung läuft über Stripe Checkout und Kundenportal.') }}</p>
            </div>
            <div class="rounded-2xl border p-6" style="border-color: var(--brand-border); background: var(--brand-card);">
                <h2 class="font-semibold text-lg" style="color: var(--brand-text);">{{ __('Wo finde ich Hilfe?') }}</h2>
                <p class="mt-3 text-sm leading-relaxed" style="color: var(--brand-text-muted);">
                    <a href="{{ route('help') }}" class="font-medium underline underline-offset-4" style="color: var(--brand-accent);">{{ __('Hilfe-Seite') }}</a>
                </p>
            </div>
        </div>
    </div>
@endsection
