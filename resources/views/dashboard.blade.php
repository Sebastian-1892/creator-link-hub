<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <p class="text-gray-600">{{ __('Willkommen zurück! Verwalte deine Bio-Seite, Links und Analytics.') }}</p>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <a href="{{ route('bio.edit') }}" wire:navigate class="block rounded-lg bg-white p-6 shadow hover:ring-2 hover:ring-indigo-500/20">
                    <div class="text-sm font-medium text-indigo-600">{{ __('Bio-Seite') }}</div>
                    <p class="mt-2 text-sm text-gray-500">{{ __('Profil, Theme, Veröffentlichung') }}</p>
                </a>
                <a href="{{ route('links.manage') }}" wire:navigate class="block rounded-lg bg-white p-6 shadow hover:ring-2 hover:ring-indigo-500/20">
                    <div class="text-sm font-medium text-indigo-600">{{ __('Links') }}</div>
                    <p class="mt-2 text-sm text-gray-500">{{ __('Smart-Links & Reihenfolge') }}</p>
                </a>
                <a href="{{ route('analytics') }}" wire:navigate class="block rounded-lg bg-white p-6 shadow hover:ring-2 hover:ring-indigo-500/20">
                    <div class="text-sm font-medium text-indigo-600">{{ __('Analytics') }}</div>
                    <p class="mt-2 text-sm text-gray-500">{{ __('Klicks & Top-Links') }}</p>
                </a>
                <a href="{{ route('billing') }}" wire:navigate class="block rounded-lg bg-white p-6 shadow hover:ring-2 hover:ring-indigo-500/20">
                    <div class="text-sm font-medium text-indigo-600">{{ __('Abrechnung') }}</div>
                    <p class="mt-2 text-sm text-gray-500">{{ __('Plan & Stripe') }}</p>
                </a>
            </div>
            @php($ws = auth()->user()->currentWorkspace())
            @if ($ws?->profile)
                <div class="rounded-lg bg-white p-6 shadow text-sm text-gray-600">
                    {{ __('Öffentliche URL') }}:
                    <a class="text-indigo-600 font-medium underline" target="_blank" href="{{ route('public.profile', $ws->profile->slug) }}">{{ route('public.profile', $ws->profile->slug) }}</a>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
