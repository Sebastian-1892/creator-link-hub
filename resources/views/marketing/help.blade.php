@extends('layouts.marketing')

@section('title', __('Hilfe'))

@section('content')
    <div class="max-w-3xl mx-auto px-4 py-16 text-slate-300 space-y-4">
        <h1 class="text-3xl font-bold text-white">{{ __('Hilfe & Support') }}</h1>
        <p>{{ __('Dokumentation und Support-Prozesse folgen mit dem Launch. Bis dahin: nutze das Dashboard unter „Links“, „Bio-Seite“ und „Analytics“.') }}</p>
        <p class="text-sm text-slate-500">{{ __('Support-E-Mail bitte in der Produktions-.env als MAIL_FROM_ADDRESS setzen.') }}</p>
    </div>
@endsection
