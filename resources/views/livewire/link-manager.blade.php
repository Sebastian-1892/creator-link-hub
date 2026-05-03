<div class="py-10">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-8">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">{{ __('Links verwalten') }}</h1>
            <p class="mt-1 text-gray-600">{{ __('Ziehe Besucher zu deinen wichtigsten Zielen — Messung erfolgt über „Intelligente Links“.') }}</p>
        </div>

        @if (session('error'))
            <div class="rounded-md bg-red-50 p-4 text-sm text-red-800">{{ session('error') }}</div>
        @endif

        <div class="bg-white shadow sm:rounded-lg p-6 space-y-4">
            <h2 class="font-medium text-gray-900">{{ __('Neuer Link') }}</h2>
            <form wire:submit="addLink" class="grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <x-input-label for="title" :value="__('Titel')" />
                    <x-text-input wire:model="newTitle" id="title" class="block mt-1 w-full" />
                    <x-input-error :messages="$errors->get('newTitle')" class="mt-2" />
                </div>
                <div class="sm:col-span-2">
                    <x-input-label for="url" :value="__('Ziel-URL')" />
                    <x-text-input wire:model="newUrl" id="url" class="block mt-1 w-full" type="url" placeholder="https://..." />
                    <x-input-error :messages="$errors->get('newUrl')" class="mt-2" />
                </div>
                <div class="sm:col-span-2 flex justify-end">
                    <x-primary-button type="submit">{{ __('Hinzufügen') }}</x-primary-button>
                </div>
            </form>
        </div>

        <div class="bg-white shadow sm:rounded-lg divide-y divide-gray-100">
            @forelse ($links as $link)
                <div class="p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <div class="font-medium text-gray-900">{{ $link->title }}</div>
                        <div class="text-sm text-gray-500 break-all">{{ $link->url }}</div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <x-secondary-button type="button" wire:click="move({{ $link->id }}, 'up')">{{ __('↑') }}</x-secondary-button>
                        <x-secondary-button type="button" wire:click="move({{ $link->id }}, 'down')">{{ __('↓') }}</x-secondary-button>
                        <x-danger-button type="button" wire:click="deleteLink({{ $link->id }})">{{ __('Löschen') }}</x-danger-button>
                    </div>
                    <div class="text-xs text-gray-400 sm:w-full sm:order-last">
                        {{ __('Tracking-URL') }}:
                        <a class="underline" href="{{ route('links.redirect', $link) }}" target="_blank">{{ route('links.redirect', $link) }}</a>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-gray-500">{{ __('Noch keine Links — leg los!') }}</div>
            @endforelse
        </div>
    </div>
</div>
