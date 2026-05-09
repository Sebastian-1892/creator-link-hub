@extends('layouts.marketing')

@section('title', __('Hilfe'))

@section('content')
    <div class="max-w-3xl mx-auto px-4 py-16 lg:py-20">
        <div class="text-center mb-10">
            <p class="text-sm font-semibold uppercase tracking-wider" style="color: var(--brand-accent);">{{ __('Support') }}</p>
            <h1 class="mt-3 text-3xl sm:text-4xl font-bold" style="color: var(--brand-text);">{{ __('Hilfe & Support') }}</h1>
        </div>
        <div
            class="rounded-2xl border p-8 space-y-4 leading-relaxed"
            style="border-color: var(--brand-border); background: var(--brand-card); color: var(--brand-text-muted);"
        >
            <p style="color: var(--brand-text);">{{ __('Dokumentation und Support-Prozesse folgen mit dem Launch. Bis dahin: nutze das Dashboard unter „Links“, „Bio-Seite“ und „Analytics“.') }}</p>
            <p class="text-sm opacity-80">{{ __('Support-E-Mail bitte in der Produktions-.env als MAIL_FROM_ADDRESS setzen.') }}</p>
        </div>
    </div>
@endsection
