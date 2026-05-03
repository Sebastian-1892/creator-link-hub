@extends('layouts.marketing')

@section('title', 'Creator Link Hub')

@section('content')
    <section class="max-w-6xl mx-auto px-4 py-20 text-center">
        <p class="text-cyan-400 text-sm font-semibold tracking-wide uppercase">{{ __('Link-in-Bio für Creator') }}</p>
        <h1 class="mt-4 text-4xl sm:text-5xl font-bold text-white leading-tight">
            {{ __('Eine Seite. Alle Kanäle. Messbare Conversions.') }}
        </h1>
        <p class="mt-6 max-w-2xl mx-auto text-lg text-slate-400">
            {{ __('Baue in Minuten eine hochkonvertierende Bio-Page, priorisiere Angebote und sieh, welche Links wirklich performen — mit sauberem Click-Tracking.') }}
        </p>
        <div class="mt-10 flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('register') }}" class="rounded-full bg-cyan-500 px-8 py-3 text-lg font-semibold text-slate-950 hover:bg-cyan-400">{{ __('Kostenlos starten') }}</a>
            <a href="{{ route('pricing') }}" class="rounded-full border border-slate-600 px-8 py-3 text-lg font-semibold text-white hover:border-slate-400">{{ __('Preise ansehen') }}</a>
        </div>
    </section>
    <section class="max-w-6xl mx-auto px-4 py-16 grid sm:grid-cols-3 gap-8 text-left">
        <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-6">
            <h3 class="font-semibold text-white">{{ __('Smart Links') }}</h3>
            <p class="mt-2 text-slate-400 text-sm">{{ __('Jeder Button kann getrackt werden — inkl. Bot-Filter und aggregiertem Dashboard.') }}</p>
        </div>
        <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-6">
            <h3 class="font-semibold text-white">{{ __('Themes') }}</h3>
            <p class="mt-2 text-slate-400 text-sm">{{ __('Schnelle Anpassung an deine Marke — Dark, Light und farbige Presets.') }}</p>
        </div>
        <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-6">
            <h3 class="font-semibold text-white">{{ __('Stripe Billing') }}</h3>
            <p class="mt-2 text-slate-400 text-sm">{{ __('Upgrade auf Starter oder Pro — Kundenportal für Rechnungen & Kündigung.') }}</p>
        </div>
    </section>
@endsection
