@extends('layouts.marketing')

@section('title', 'FAQ')

@section('content')
    <div class="max-w-3xl mx-auto px-4 py-16 space-y-8">
        <h1 class="text-3xl font-bold text-white">FAQ</h1>
        <div class="space-y-6 text-slate-300">
            <div>
                <h2 class="font-semibold text-white">{{ __('Wie funktioniert Click-Tracking?') }}</h2>
                <p class="mt-2 text-sm">{{ __('Besucher klicken auf einen Smart-Link, der über /go/{id} läuft. Wir zählen den Klick (ohne Klartext-IP) und leiten weiter.') }}</p>
            </div>
            <div>
                <h2 class="font-semibold text-white">{{ __('Kann ich später upgraden?') }}</h2>
                <p class="mt-2 text-sm">{{ __('Ja — Abrechnung läuft über Stripe Checkout und Kundenportal.') }}</p>
            </div>
            <div>
                <h2 class="font-semibold text-white">{{ __('Wo finde ich Hilfe?') }}</h2>
                <p class="mt-2 text-sm"><a href="{{ route('help') }}" class="text-cyan-400 underline">{{ __('Hilfe-Seite') }}</a></p>
            </div>
        </div>
    </div>
@endsection
