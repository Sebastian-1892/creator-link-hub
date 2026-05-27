<div
    class="py-10"
    x-on:close-modal.window="if ($event.detail === 'add-link') { $wire.clearPreset() }"
>
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">{{ __('Links verwalten') }}</h1>
                <p class="mt-1 text-gray-600">{{ __('Ziehe Besucher zu deinen wichtigsten Zielen — Messung erfolgt über „Intelligente Links“.') }}</p>
            </div>
            <x-primary-button
                type="button"
                x-data=""
                x-on:click="$dispatch('open-modal', 'add-link')"
                class="shrink-0"
            >
                {{ __('+ Link hinzufügen') }}
            </x-primary-button>
        </div>

        @if (session('error'))
            <div class="rounded-md bg-red-50 p-4 text-sm text-red-800">{{ session('error') }}</div>
        @endif

        <div class="bg-white shadow sm:rounded-lg overflow-hidden divide-y divide-gray-100">
            @forelse ($links as $link)
                @php
                    $brandColor = $link->brandColor();
                @endphp
                <div class="p-4 space-y-3" wire:key="link-row-{{ $link->id }}">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start">
                        <div
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full"
                            style="background-color: {{ $brandColor }}18; color: {{ $brandColor }};"
                        >
                            <x-brand-icon :name="$link->iconName()" class="h-5 w-5" />
                        </div>

                        <div class="min-w-0 flex-1 space-y-2">
                            <div>
                                <x-input-label :for="'link-title-'.$link->id" :value="__('Anzeigename auf der Bio-Seite')" class="text-xs" />
                                <x-text-input
                                    wire:model.blur="linkTitles.{{ $link->id }}"
                                    id="link-title-{{ $link->id }}"
                                    class="block mt-1 w-full"
                                />
                                <x-input-error :messages="$errors->get('linkTitles.'.$link->id)" class="mt-1" />
                            </div>
                            <p class="text-sm text-gray-500 truncate" title="{{ $link->url }}">{{ $link->url }}</p>
                        </div>

                        <div class="flex flex-wrap items-center gap-2 sm:shrink-0 sm:pt-6">
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                                <input
                                    type="checkbox"
                                    wire:model.live="linkShowIcons.{{ $link->id }}"
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                />
                                {{ __('Icon anzeigen') }}
                            </label>
                            <x-secondary-button type="button" wire:click="move({{ $link->id }}, 'up')" title="{{ __('Nach oben') }}">{{ __('↑') }}</x-secondary-button>
                            <x-secondary-button type="button" wire:click="move({{ $link->id }}, 'down')" title="{{ __('Nach unten') }}">{{ __('↓') }}</x-secondary-button>
                            <x-danger-button type="button" wire:click="deleteLink({{ $link->id }})">{{ __('Löschen') }}</x-danger-button>
                        </div>
                    </div>

                    <p class="text-xs text-gray-400 pl-0 sm:pl-[3.75rem]">
                        {{ __('Tracking-URL') }}:
                        <a class="underline break-all" href="{{ route('links.redirect', $link) }}" target="_blank" rel="noopener noreferrer">{{ route('links.redirect', $link) }}</a>
                    </p>
                </div>
            @empty
                <div class="p-10 text-center">
                    <p class="text-gray-500">{{ __('Noch keine Links — leg los!') }}</p>
                    <x-primary-button
                        type="button"
                        class="mt-4"
                        x-data=""
                        x-on:click="$dispatch('open-modal', 'add-link')"
                    >
                        {{ __('+ Link hinzufügen') }}
                    </x-primary-button>
                </div>
            @endforelse
        </div>
    </div>

    <x-modal name="add-link" maxWidth="lg" focusable>
        <div class="p-6">
            @if ($presetKey === null)
                <div class="flex items-center justify-between gap-4">
                    <h2 class="text-lg font-semibold text-gray-900">{{ __('Was möchtest du verlinken?') }}</h2>
                    <button
                        type="button"
                        wire:click="closeAddModal"
                        class="text-gray-400 hover:text-gray-600 text-2xl leading-none"
                        aria-label="{{ __('Schließen') }}"
                    >&times;</button>
                </div>
                <div class="mt-4 grid grid-cols-2 sm:grid-cols-3 gap-2">
                    @foreach ($linkPresets as $key => $preset)
                        <button
                            type="button"
                            wire:click="selectPreset('{{ $key }}')"
                            class="flex flex-col items-center gap-2 rounded-xl border border-gray-200 bg-white p-4 text-center transition hover:border-indigo-400 hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                        >
                            <x-brand-icon :name="$preset['icon'] ?? $key" class="h-8 w-8 text-gray-700" />
                            <span class="text-sm font-medium text-gray-900">{{ $preset['label'] }}</span>
                        </button>
                    @endforeach
                </div>
            @else
                @php
                    $preset = $linkPresets[$presetKey] ?? null;
                    $presetType = is_array($preset) ? ($preset['type'] ?? 'custom') : 'custom';
                @endphp

                @if (is_array($preset))
                    <button
                        type="button"
                        wire:click="clearPreset"
                        class="text-sm text-gray-500 hover:text-gray-800"
                    >&larr; {{ __('Zurück') }}</button>

                    <div class="mt-3 flex items-center gap-3">
                        <x-brand-icon :name="$preset['icon'] ?? $presetKey" class="h-8 w-8 text-gray-700 shrink-0" />
                        <h2 class="text-lg font-semibold text-gray-900">{{ $preset['label'] }}</h2>
                    </div>

                    <form wire:submit="addPresetLink" class="mt-5 space-y-4">
                        @if ($presetType === 'custom')
                            <div>
                                <x-input-label for="modal-title" :value="__('Anzeigename auf der Bio-Seite')" />
                                <x-text-input wire:model="newTitle" id="modal-title" class="block mt-1 w-full" />
                                <x-input-error :messages="$errors->get('newTitle')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="modal-url" :value="__('Ziel-URL')" />
                                <x-text-input wire:model="newUrl" id="modal-url" class="block mt-1 w-full" type="url" placeholder="https://..." />
                                <x-input-error :messages="$errors->get('newUrl')" class="mt-2" />
                            </div>
                        @else
                            <div>
                                <x-input-label for="modal-display-title" :value="__('Anzeigename auf der Bio-Seite')" />
                                <x-text-input
                                    wire:model="newTitle"
                                    id="modal-display-title"
                                    class="block mt-1 w-full"
                                    placeholder="{{ $preset['label'] }}"
                                />
                                <p class="mt-1 text-xs text-gray-500">{{ __('Leer lassen für „:label“', ['label' => $preset['label']]) }}</p>
                            </div>
                            <div>
                                <x-input-label for="preset-value" :value="__('Eingabe')" />
                                <div class="mt-1 flex rounded-md shadow-sm">
                                    @if (! empty($preset['prefix']))
                                        <span class="inline-flex items-center rounded-l-md border border-r-0 border-gray-300 bg-gray-50 px-3 text-sm text-gray-500">{{ $preset['prefix'] }}</span>
                                        <x-text-input
                                            wire:model="presetValue"
                                            id="preset-value"
                                            class="rounded-l-none block w-full"
                                            placeholder="{{ $preset['placeholder'] ?? '' }}"
                                        />
                                    @else
                                        <x-text-input
                                            wire:model="presetValue"
                                            id="preset-value"
                                            class="block w-full"
                                            placeholder="{{ $preset['placeholder'] ?? '' }}"
                                            @if ($presetType === 'url') type="url" @endif
                                            @if ($presetType === 'email') type="email" @endif
                                            @if ($presetType === 'phone') type="tel" @endif
                                        />
                                    @endif
                                </div>
                                <x-input-error :messages="$errors->get('presetValue')" class="mt-2" />
                            </div>
                        @endif

                        <div class="flex justify-end gap-3 pt-2">
                            <x-secondary-button type="button" wire:click="closeAddModal">
                                {{ __('Abbrechen') }}
                            </x-secondary-button>
                            <x-primary-button type="submit" wire:loading.attr="disabled" wire:target="addPresetLink">
                                <span wire:loading.remove wire:target="addPresetLink">{{ __('Hinzufügen') }}</span>
                                <span wire:loading wire:target="addPresetLink">{{ __('Wird hinzugefügt…') }}</span>
                            </x-primary-button>
                        </div>
                    </form>
                @endif
            @endif
        </div>
    </x-modal>
</div>
