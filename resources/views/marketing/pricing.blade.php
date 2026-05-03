@extends('layouts.marketing')

@section('title', __('Preise'))

@section('content')
    <div class="max-w-5xl mx-auto px-4 py-16">
        <h1 class="text-3xl font-bold text-white text-center">{{ __('Preise') }}</h1>
        <p class="mt-3 text-center text-slate-400 max-w-2xl mx-auto">{{ __('Starte kostenlos und upgrade, wenn du mehr brauchst.') }}</p>
        <div class="mt-12 grid gap-6 md:grid-cols-3">
            <div class="rounded-2xl border border-slate-800 bg-slate-900/50 p-6 flex flex-col">
                <h2 class="text-lg font-semibold text-white">Free</h2>
                <p class="mt-2 text-3xl font-bold text-white">0 €</p>
                <ul class="mt-6 space-y-2 text-sm text-slate-400 flex-1">
                    <li>• {{ __('1 Profil') }}</li>
                    <li>• {{ __('Bis zu 10 Links') }}</li>
                    <li>• {{ __('Basis-Analytics') }}</li>
                    <li>• {{ __('Plattform-Branding') }}</li>
                </ul>
                <a href="{{ route('register') }}" class="mt-6 block text-center rounded-full border border-slate-600 py-2 text-sm font-medium text-white hover:border-slate-400">{{ __('Starten') }}</a>
            </div>
            <div class="rounded-2xl border border-cyan-500/40 bg-slate-900/80 p-6 flex flex-col ring-1 ring-cyan-500/30">
                <h2 class="text-lg font-semibold text-cyan-300">Starter</h2>
                <p class="mt-2 text-3xl font-bold text-white">9 € <span class="text-base font-normal text-slate-400">/ {{ __('Monat') }}</span></p>
                <ul class="mt-6 space-y-2 text-sm text-slate-300 flex-1">
                    <li>• {{ __('Unbegrenzte Links') }}</li>
                    <li>• {{ __('Ohne Plattform-Branding') }}</li>
                    <li>• {{ __('Eigene Domain (Roadmap)') }}</li>
                </ul>
                <a href="{{ route('register') }}" class="mt-6 block text-center rounded-full bg-cyan-500 py-2 text-sm font-semibold text-slate-950 hover:bg-cyan-400">{{ __('Upgrade im Dashboard') }}</a>
            </div>
            <div class="rounded-2xl border border-slate-800 bg-slate-900/50 p-6 flex flex-col">
                <h2 class="text-lg font-semibold text-white">Pro</h2>
                <p class="mt-2 text-3xl font-bold text-white">24 € <span class="text-base font-normal text-slate-400">/ {{ __('Monat') }}</span></p>
                <ul class="mt-6 space-y-2 text-sm text-slate-400 flex-1">
                    <li>• {{ __('Alles aus Starter') }}</li>
                    <li>• {{ __('Referrals & Rewards (Roadmap)') }}</li>
                    <li>• {{ __('UTM & Conversion (Roadmap)') }}</li>
                </ul>
                <a href="{{ route('register') }}" class="mt-6 block text-center rounded-full border border-slate-600 py-2 text-sm font-medium text-white hover:border-slate-400">{{ __('Upgrade im Dashboard') }}</a>
            </div>
        </div>
    </div>
@endsection
