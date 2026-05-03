@extends('layouts.marketing')

@section('title', __('Datenschutz'))

@section('content')
    <div class="max-w-3xl mx-auto px-4 py-16 text-slate-300 space-y-4 text-sm leading-relaxed">
        <h1 class="text-2xl font-bold text-white">{{ __('Datenschutzerklärung') }}</h1>
        <p>{{ __('Diese Vorlage ersetzt keine Rechtsberatung. Bitte von einer Kanzlei für SaaS/DSGVO freigeben lassen.') }}</p>
        <h2 class="text-lg font-semibold text-white pt-4">{{ __('Verantwortlicher') }}</h2>
        <p>…</p>
        <h2 class="text-lg font-semibold text-white pt-4">{{ __('Hosting & Logs') }}</h2>
        <p>{{ __('Server-Logs, Sicherheit, Rate-Limits.') }}</p>
        <h2 class="text-lg font-semibold text-white pt-4">{{ __('Stripe & Zahlungen') }}</h2>
        <p>{{ __('Zahlungsabwicklung durch Stripe — siehe AVV bei Stripe.') }}</p>
        <h2 class="text-lg font-semibold text-white pt-4">{{ __('Click-Tracking') }}</h2>
        <p>{{ __('Wir speichern gehashte IP, User-Agent und Zeitstempel zur Missbrauchsprävention und Statistik.') }}</p>
        <h2 class="text-lg font-semibold text-white pt-4">{{ __('Auftragsverarbeitung') }}</h2>
        <p>{{ __('Links zu AVV: Stripe, Postmark/Resend, Hosting-Anbieter.') }}</p>
    </div>
@endsection
