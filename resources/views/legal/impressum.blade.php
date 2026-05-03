@extends('layouts.marketing')

@section('title', __('Impressum'))

@section('content')
    <div class="max-w-3xl mx-auto px-4 py-16 prose prose-invert">
        <h1 class="text-2xl font-bold text-white">{{ __('Impressum') }}</h1>
        <p class="text-slate-400 mt-4">{{ __('Angaben gemäß § 5 TMG — Platzhalter. Bitte Firmendaten, Adresse und Kontakt vor Go-Live eintragen.') }}</p>
        <ul class="mt-4 text-slate-300 text-sm space-y-2">
            <li><strong>{{ __('Name') }}:</strong> …</li>
            <li><strong>{{ __('Adresse') }}:</strong> …</li>
            <li><strong>{{ __('E-Mail') }}:</strong> …</li>
            <li><strong>{{ __('USt-IdNr.') }}:</strong> …</li>
        </ul>
    </div>
@endsection
