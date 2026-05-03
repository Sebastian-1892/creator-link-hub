<div class="py-10">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <h1 class="text-2xl font-semibold text-gray-900">{{ __('Abrechnung') }}</h1>

        @if (session('error'))
            <div class="rounded-md bg-red-50 p-4 text-sm text-red-800">{{ session('error') }}</div>
        @endif

        @if (request('checkout') === 'success')
            <div class="rounded-md bg-green-50 p-4 text-sm text-green-800">{{ __('Checkout abgeschlossen — danke!') }}</div>
        @endif

        <div class="bg-white shadow sm:rounded-lg p-6 space-y-4">
            <p class="text-gray-600">{{ __('Aktueller Plan') }}: <span class="font-semibold text-gray-900">{{ $workspace->plan }}</span></p>

            @if ($subscription && $subscription->valid())
                <p class="text-sm text-gray-500">{{ __('Aktives Abonnement — nutze das Kundenportal für Rechnungen und Kündigung.') }}</p>
                <x-secondary-button type="button" wire:click="portal">{{ __('Stripe-Kundenportal öffnen') }}</x-secondary-button>
            @else
                <div class="grid gap-3 sm:grid-cols-2">
                    <div class="rounded-lg border border-gray-200 p-4">
                        <div class="font-medium text-gray-900">Starter</div>
                        <p class="text-sm text-gray-600 mt-1">{{ __('Unbegrenzte Links, eigenes Branding entfernen (Price ID in .env)') }}</p>
                        <x-primary-button class="mt-3" type="button" wire:click="checkout('starter')">{{ __('Starter wählen') }}</x-primary-button>
                    </div>
                    <div class="rounded-lg border border-gray-200 p-4">
                        <div class="font-medium text-gray-900">Pro</div>
                        <p class="text-sm text-gray-600 mt-1">{{ __('Erweiterte Analytics & Automation (Price ID in .env)') }}</p>
                        <x-primary-button class="mt-3" type="button" wire:click="checkout('pro')">{{ __('Pro wählen') }}</x-primary-button>
                    </div>
                </div>
            @endif
        </div>

        <p class="text-xs text-gray-500">{{ __('Hinweis: Lege Test-Keys und Price-IDs in der .env an und nutze die Stripe-Dokumentation für Steuern (Stripe Tax).') }}</p>
    </div>
</div>
