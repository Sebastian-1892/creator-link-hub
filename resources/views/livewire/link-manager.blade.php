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
            <div class="flex gap-2">
                <x-secondary-button type="button" wire:click="openCollectionModal" class="shrink-0">
                    {{ __('+ Collection') }}
                </x-secondary-button>
                <x-primary-button
                    type="button"
                    x-data=""
                    x-on:click="$dispatch('open-modal', 'add-link')"
                    class="shrink-0"
                >
                    {{ __('+ Link hinzufügen') }}
                </x-primary-button>
            </div>
        </div>

        @if (session('error'))
            <div class="rounded-md bg-red-50 p-4 text-sm text-red-800">{{ session('error') }}</div>
        @endif

        @if (session('status'))
            <div class="rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('status') }}</div>
        @endif

        @php
            $collectionLinks = $links->where('link_type', 'collection')->values();
            $normalLinks = $links->where('link_type', '!=', 'collection')->values();
        @endphp

        <div class="space-y-6">
            <section class="bg-white shadow sm:rounded-lg overflow-hidden divide-y divide-gray-100">
                <div class="px-4 py-3 bg-gray-50 border-b border-gray-100">
                    <h2 class="text-sm font-semibold text-gray-900">{{ __('Normale Links') }}</h2>
                </div>
                @forelse ($normalLinks as $link)
                @php
                    $brandColor = $link->brandColor();
                    $isCollection = $link->isCollection();
                    $products = $productsByCollection[$link->id] ?? collect();
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
                            @if ($isCollection)
                                <p class="text-sm text-gray-500">{{ __('Collection mit :count Produkten', ['count' => $products->count()]) }}</p>
                            @else
                                <p class="text-sm text-gray-500 truncate" title="{{ $link->url }}">{{ $link->url }}</p>
                            @endif
                        </div>

                        <div class="flex flex-wrap items-center gap-2 sm:shrink-0 sm:pt-6">
                            @if (! $isCollection)
                                <label class="inline-flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                                    <input
                                        type="checkbox"
                                        wire:model.live="linkShowIcons.{{ $link->id }}"
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                    />
                                    {{ __('Icon anzeigen') }}
                                </label>
                            @endif
                            @if ($isCollection)
                                <x-secondary-button type="button" wire:click="openProductModal({{ $link->id }})">{{ __('+ Produkt') }}</x-secondary-button>
                            @endif
                            <x-secondary-button type="button" wire:click="move({{ $link->id }}, 'up')" title="{{ __('Nach oben') }}">{{ __('↑') }}</x-secondary-button>
                            <x-secondary-button type="button" wire:click="move({{ $link->id }}, 'down')" title="{{ __('Nach unten') }}">{{ __('↓') }}</x-secondary-button>
                            <x-danger-button type="button" wire:click="deleteLink({{ $link->id }})">{{ __('Löschen') }}</x-danger-button>
                        </div>
                    </div>

                    @if (! $isCollection)
                        <p class="text-xs text-gray-400 pl-0 sm:pl-[3.75rem]">
                            {{ __('Tracking-URL') }}:
                            <a class="underline break-all" href="{{ route('links.redirect', $link) }}" target="_blank" rel="noopener noreferrer">{{ route('links.redirect', $link) }}</a>
                        </p>
                    @endif

                    @if ($isCollection && $products->isNotEmpty())
                        <div class="pl-0 sm:pl-[3.75rem] space-y-2">
                            @foreach ($products as $product)
                                <div class="rounded-lg border border-gray-200 p-3 flex items-center gap-3" wire:key="product-{{ $product->id }}">
                                    <div class="h-10 w-10 rounded-md overflow-hidden bg-gray-100 shrink-0">
                                        @if ($product->image_url)
                                            <img src="{{ $product->image_url }}" alt="" class="h-full w-full object-cover" loading="lazy" />
                                        @endif
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium text-gray-900 truncate">{{ $product->title }}</p>
                                        <p class="text-xs text-gray-500 truncate">{{ $product->url }}</p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <x-secondary-button type="button" wire:click="move({{ $product->id }}, 'up')">{{ __('↑') }}</x-secondary-button>
                                        <x-secondary-button type="button" wire:click="move({{ $product->id }}, 'down')">{{ __('↓') }}</x-secondary-button>
                                        <x-danger-button type="button" wire:click="deleteLink({{ $product->id }})">{{ __('Löschen') }}</x-danger-button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
                @empty
                    <div class="p-8 text-center text-sm text-gray-500">{{ __('Noch keine normalen Links vorhanden.') }}</div>
                @endforelse
            </section>

            <section class="bg-white shadow sm:rounded-lg overflow-hidden divide-y divide-gray-100">
                <div class="px-4 py-3 bg-indigo-50 border-b border-indigo-100">
                    <h2 class="text-sm font-semibold text-indigo-900">{{ __('Shop Collections') }}</h2>
                    <p class="mt-0.5 text-xs text-indigo-700">{{ __('Collections gruppieren Produkte, die auf externe Shop-URLs verweisen.') }}</p>
                </div>
                @forelse ($collectionLinks as $link)
                    @php
                        $products = $productsByCollection[$link->id] ?? collect();
                    @endphp
                    <div class="p-4 space-y-3" wire:key="collection-row-{{ $link->id }}">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start">
                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-indigo-700">
                                <x-brand-icon :name="$link->iconName()" class="h-5 w-5" />
                            </div>

                            <div class="min-w-0 flex-1 space-y-2">
                                <div>
                                    <x-input-label :for="'link-title-'.$link->id" :value="__('Collection-Name')" class="text-xs" />
                                    <x-text-input
                                        wire:model.blur="linkTitles.{{ $link->id }}"
                                        id="link-title-{{ $link->id }}"
                                        class="block mt-1 w-full"
                                    />
                                    <x-input-error :messages="$errors->get('linkTitles.'.$link->id)" class="mt-1" />
                                </div>
                                <p class="text-sm text-gray-500">{{ __(':count Produkte', ['count' => $products->count()]) }}</p>
                            </div>

                            <div class="flex flex-wrap items-center gap-2 sm:shrink-0 sm:pt-6">
                                <x-secondary-button type="button" wire:click="openProductModal({{ $link->id }})">{{ __('+ Produkt') }}</x-secondary-button>
                                <x-secondary-button type="button" wire:click="move({{ $link->id }}, 'up')" title="{{ __('Nach oben') }}">{{ __('↑') }}</x-secondary-button>
                                <x-secondary-button type="button" wire:click="move({{ $link->id }}, 'down')" title="{{ __('Nach unten') }}">{{ __('↓') }}</x-secondary-button>
                                <x-danger-button type="button" wire:click="deleteLink({{ $link->id }})">{{ __('Löschen') }}</x-danger-button>
                            </div>
                        </div>

                        @if ($products->isNotEmpty())
                            <div class="pl-0 sm:pl-[3.75rem] grid grid-cols-1 sm:grid-cols-2 gap-2">
                                @foreach ($products as $product)
                                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-3 flex items-center gap-3" wire:key="product-{{ $product->id }}">
                                        <div class="h-10 w-10 rounded-md overflow-hidden bg-gray-200 shrink-0">
                                            @if ($product->image_url)
                                                <img src="{{ $product->image_url }}" alt="" class="h-full w-full object-cover" loading="lazy" />
                                            @endif
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-medium text-gray-900 truncate">{{ $product->title }}</p>
                                            <p class="text-xs text-gray-500 truncate">{{ $product->url }}</p>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <x-secondary-button type="button" wire:click="move({{ $product->id }}, 'up')">{{ __('↑') }}</x-secondary-button>
                                            <x-secondary-button type="button" wire:click="move({{ $product->id }}, 'down')">{{ __('↓') }}</x-secondary-button>
                                            <x-danger-button type="button" wire:click="deleteLink({{ $product->id }})">{{ __('Löschen') }}</x-danger-button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="p-8 text-center">
                        <p class="text-gray-500">{{ __('Noch keine Collection vorhanden.') }}</p>
                        <x-secondary-button type="button" class="mt-3" wire:click="openCollectionModal">
                            {{ __('+ Collection anlegen') }}
                        </x-secondary-button>
                    </div>
                @endforelse
            </section>
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
                            <span class="text-sm font-medium text-gray-900">{{ __('presets.'.$key) }}</span>
                        </button>
                    @endforeach
                </div>
            @else
                @php
                    $preset = $linkPresets[$presetKey] ?? null;
                    $presetType = is_array($preset) ? ($preset['type'] ?? 'custom') : 'custom';
                    $presetInputType = match ($presetType) {
                        'url' => 'url',
                        'email' => 'email',
                        'phone' => 'tel',
                        default => 'text',
                    };
                @endphp

                @if (is_array($preset))
                    <button
                        type="button"
                        wire:click="clearPreset"
                        class="text-sm text-gray-500 hover:text-gray-800"
                    >&larr; {{ __('Zurück') }}</button>

                    <div class="mt-3 flex items-center gap-3">
                        <x-brand-icon :name="$preset['icon'] ?? $presetKey" class="h-8 w-8 text-gray-700 shrink-0" />
                        <h2 class="text-lg font-semibold text-gray-900">{{ __('presets.'.$presetKey) }}</h2>
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
                        @elseif ($presetType === 'spotify')
                            <div>
                                <x-input-label for="modal-display-title" :value="__('Anzeigename auf der Bio-Seite')" />
                                <x-text-input
                                    wire:model="newTitle"
                                    id="modal-display-title"
                                    class="block mt-1 w-full"
                                    placeholder="{{ __('presets.spotify') }}"
                                />
                                <p class="mt-1 text-xs text-gray-500">{{ __('Leer lassen für „:label“', ['label' => __('presets.spotify')]) }}</p>
                            </div>

                            <div>
                                <x-input-label for="preset-value" :value="__('Spotify-URL oder URI')" />
                                <x-text-input
                                    wire:model.live="presetValue"
                                    id="preset-value"
                                    class="block mt-1 w-full"
                                    type="text"
                                    placeholder="{{ $preset['placeholder'] ?? '' }}"
                                />
                                <p class="mt-1 text-xs text-gray-500">
                                    {{ __('Unterstützt: Track, Episode, Show, Playlist, Album, Artist — als https://open.spotify.com/… oder spotify:track:…') }}
                                </p>
                                <x-input-error :messages="$errors->get('presetValue')" class="mt-2" />
                            </div>

                            @if ($spotifyPreview)
                                <div class="rounded-lg border border-green-200 bg-green-50 p-3 text-sm text-green-900">
                                    <p class="font-medium">{{ __('Vorschau erkannt') }}</p>
                                    <p class="mt-1 capitalize">{{ $spotifyPreview['resource_type'] }} · {{ $spotifyPreview['provider_id'] }}</p>
                                    <p class="mt-1 text-xs break-all opacity-80">{{ $spotifyPreview['canonical_url'] }}</p>
                                </div>
                            @elseif (trim($presetValue) !== '')
                                <div class="rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900">
                                    {{ __('Noch keine gültige Spotify-URL erkannt.') }}
                                </div>
                            @endif

                            <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 space-y-3">
                                <div class="flex items-start gap-3">
                                    <input
                                        type="checkbox"
                                        wire:model.live="spotifyDynamic"
                                        id="spotify-dynamic"
                                        class="mt-1 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                    />
                                    <div>
                                        <label for="spotify-dynamic" class="text-sm font-medium text-gray-900 cursor-pointer">
                                            {{ __('Automatisch aktuellen/zuletzt gespielten Titel anzeigen') }}
                                        </label>
                                        <p class="mt-1 text-xs text-gray-500">
                                            {{ __('Erfordert Spotify-Verbindung. Es werden nur die Scopes „Aktuell gespielt“ und „Zuletzt gespielt“ abgefragt.') }}
                                        </p>
                                    </div>
                                </div>
                                <x-input-error :messages="$errors->get('spotifyDynamic')" class="mt-1" />

                                @if ($spotifyDynamic)
                                    @if ($spotifyAccount?->isConnected())
                                        <div class="flex flex-wrap items-center gap-3">
                                            <span class="text-sm text-green-700">{{ __('Spotify verbunden') }}</span>
                                            <form method="POST" action="{{ route('spotify.disconnect') }}">
                                                @csrf
                                                <x-secondary-button type="submit">{{ __('Trennen') }}</x-secondary-button>
                                            </form>
                                        </div>
                                    @elseif ($spotifyConfigured)
                                        <a href="{{ route('spotify.connect') }}">
                                            <x-primary-button type="button">{{ __('Mit Spotify verbinden') }}</x-primary-button>
                                        </a>
                                    @else
                                        <p class="text-sm text-amber-700">{{ __('Spotify OAuth ist noch nicht konfiguriert (SPOTIFY_CLIENT_ID).') }}</p>
                                    @endif

                                    @if ($spotifyAccount && ! $spotifyAccount->isConnected())
                                        <p class="text-sm text-amber-700">{{ __('Spotify-Verbindung abgelaufen — bitte erneut verbinden.') }}</p>
                                        @if ($spotifyConfigured)
                                            <a href="{{ route('spotify.connect') }}">
                                                <x-primary-button type="button">{{ __('Erneut verbinden') }}</x-primary-button>
                                            </a>
                                        @endif
                                    @endif

                                    <p class="text-xs text-gray-500">
                                        {{ __('Datenschutzhinweis: Wir speichern OAuth-Tokens verschlüsselt und lesen nur deinen aktuellen/zuletzt gespielten Titel.') }}
                                        <a href="{{ route('legal.datenschutz') }}" class="underline" target="_blank" rel="noopener noreferrer">{{ __('Datenschutz') }}</a>
                                    </p>
                                @endif
                            </div>
                        @else
                            <div>
                                <x-input-label for="modal-display-title" :value="__('Anzeigename auf der Bio-Seite')" />
                                <x-text-input
                                    wire:model="newTitle"
                                    id="modal-display-title"
                                    class="block mt-1 w-full"
                                    placeholder="{{ __('presets.'.$presetKey) }}"
                                />
                                <p class="mt-1 text-xs text-gray-500">{{ __('Leer lassen für „:label“', ['label' => __('presets.'.$presetKey)]) }}</p>
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
                                            type="{{ $presetInputType }}"
                                            placeholder="{{ $preset['placeholder'] ?? '' }}"
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

    <x-modal name="add-collection" maxWidth="md" focusable>
        <div class="p-6">
            <h2 class="text-lg font-semibold text-gray-900">{{ __('Collection anlegen') }}</h2>
            <p class="mt-1 text-sm text-gray-500">{{ __('Beispiel: Merch, Kurse, Empfehlungen') }}</p>
            <form wire:submit="createCollection" class="mt-4 space-y-4">
                <div>
                    <x-input-label for="collection-title" :value="__('Collection-Name')" />
                    <x-text-input wire:model="collectionTitle" id="collection-title" class="block mt-1 w-full" />
                    <x-input-error :messages="$errors->get('collectionTitle')" class="mt-2" />
                </div>
                <div class="flex justify-end gap-3">
                    <x-secondary-button type="button" x-data="" x-on:click="$dispatch('close-modal', 'add-collection')">{{ __('Abbrechen') }}</x-secondary-button>
                    <x-primary-button type="submit">{{ __('Anlegen') }}</x-primary-button>
                </div>
            </form>
        </div>
    </x-modal>

    <x-modal name="add-product" maxWidth="lg" focusable>
        <div class="p-6">
            <h2 class="text-lg font-semibold text-gray-900">{{ __('Produkt hinzufügen') }}</h2>
            <form wire:submit="createProduct" class="mt-4 space-y-4">
                <div>
                    <x-input-label for="product-title" :value="__('Produktname')" />
                    <x-text-input wire:model="productTitle" id="product-title" class="block mt-1 w-full" />
                    <x-input-error :messages="$errors->get('productTitle')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="product-url" :value="__('Produkt-Link')" />
                    <x-text-input wire:model="productUrl" id="product-url" type="url" class="block mt-1 w-full" placeholder="https://..." />
                    <x-input-error :messages="$errors->get('productUrl')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="product-image-url" :value="__('Bild-URL (optional)')" />
                    <x-text-input wire:model="productImageUrl" id="product-image-url" type="url" class="block mt-1 w-full" placeholder="https://..." />
                    <x-input-error :messages="$errors->get('productImageUrl')" class="mt-2" />
                </div>
                <div class="flex justify-end gap-3">
                    <x-secondary-button type="button" x-data="" x-on:click="$dispatch('close-modal', 'add-product')">{{ __('Abbrechen') }}</x-secondary-button>
                    <x-primary-button type="submit">{{ __('Produkt hinzufügen') }}</x-primary-button>
                </div>
            </form>
        </div>
    </x-modal>
</div>
