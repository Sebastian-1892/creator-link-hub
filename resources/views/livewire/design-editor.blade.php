<div class="py-10">
    @if ($saveNotice)
        <div class="fixed top-20 left-0 right-0 z-50 flex justify-center px-4 pointer-events-none" role="status">
            <div class="pointer-events-auto flex max-w-lg items-center gap-3 rounded-xl border border-green-600 bg-green-600 px-5 py-4 text-white shadow-xl">
                <p class="text-base font-semibold">{{ $saveNotice }}</p>
                <button type="button" wire:click="dismissSaveNotice" class="rounded-md p-1 hover:bg-green-700" aria-label="{{ __('Schließen') }}">×</button>
            </div>
        </div>
    @endif

    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">{{ __('Design') }}</h1>
                <p class="mt-1 text-gray-600">{{ __('Passe Theme, Header, Hintergrund, Schrift und Buttons deiner Bio-Seite an.') }}</p>
            </div>
            @if ($publicUrl)
                <a href="{{ $publicUrl }}" target="_blank" rel="noopener noreferrer" class="text-sm font-medium text-indigo-600 hover:text-indigo-800 shrink-0">
                    {{ __('Öffentliche Seite ansehen') }} →
                </a>
            @endif
        </div>

        @if (session('design_notice'))
            <div class="mt-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-900">{{ session('design_notice') }}</div>
        @endif

        <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-[12rem_minmax(0,1fr)_18.75rem] xl:items-start">
            {{-- Sektions-Navigation --}}
            <nav class="bg-white shadow sm:rounded-lg p-3 h-fit space-y-1" aria-label="{{ __('Design-Bereiche') }}">
                @foreach ([
                    'theme' => __('Theme'),
                    'header' => __('Header'),
                    'wallpaper' => __('Wallpaper'),
                    'text' => __('Text'),
                    'buttons' => __('Buttons'),
                ] as $key => $label)
                    <button
                        type="button"
                        wire:click="setSection('{{ $key }}')"
                        @class([
                            'w-full rounded-lg px-3 py-2 text-left text-sm font-medium transition',
                            'bg-indigo-50 text-indigo-800' => $activeSection === $key,
                            'text-gray-700 hover:bg-gray-50' => $activeSection !== $key,
                        ])
                    >{{ $label }}</button>
                @endforeach
            </nav>

            {{-- Einstellungen --}}
            <div class="bg-white shadow sm:rounded-lg p-6 min-h-[28rem] min-w-0">
                <form wire:submit="save" id="design-settings-form">
                @if ($activeSection === 'theme')
                    <h2 class="text-lg font-semibold text-gray-900">{{ __('Profil-Vorlage') }}</h2>
                    <p class="mt-1 text-sm text-gray-500">{{ __('Wähle Layout und Farben — die Live-Vorschau rechts aktualisiert sich sofort.') }}</p>
                    <div class="mt-3 flex flex-wrap gap-2" role="tablist" aria-label="{{ __('Filter') }}">
                        @foreach ([
                            'all' => __('Alle'),
                            'light' => __('Hell'),
                            'dark' => __('Dunkel'),
                            'colorful' => __('Bunt'),
                            'minimal' => __('Minimal'),
                        ] as $key => $label)
                            <button
                                type="button"
                                wire:click="$set('theme_filter', '{{ $key }}')"
                                @class([
                                    'rounded-full px-4 py-1.5 text-sm font-medium border transition',
                                    'border-indigo-600 bg-indigo-50 text-indigo-800' => $theme_filter === $key,
                                    'border-gray-200 bg-white text-gray-700 hover:border-gray-300' => $theme_filter !== $key,
                                ])
                            >{{ $label }}</button>
                        @endforeach
                    </div>
                    <div class="mt-4">
                        @include('livewire.partials.theme-picker-cards', ['themes' => $themes, 'theme_id' => $theme_id])
                    </div>
                    <x-input-error :messages="$errors->get('theme_id')" class="mt-2" />
                @endif

                @if ($activeSection === 'header')
                    <h2 class="text-lg font-semibold text-gray-900">{{ __('Header') }}</h2>
                    <p class="mt-1 text-sm text-gray-500">{{ __('Layout für Profilbild und Name.') }}</p>
                    <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-3">
                        @foreach (['classic' => __('Classic'), 'hero' => __('Hero'), 'banner' => __('Banner')] as $value => $label)
                            <label @class(['cursor-pointer rounded-xl border-2 p-4 text-center', 'border-indigo-600 bg-indigo-50' => $header_layout === $value, 'border-gray-200' => $header_layout !== $value])>
                                <input type="radio" wire:model.live="header_layout" value="{{ $value }}" class="sr-only" />
                                <span class="text-sm font-semibold">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                @endif

                @if ($activeSection === 'wallpaper')
                    <h2 class="text-lg font-semibold text-gray-900">{{ __('Wallpaper') }}</h2>
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach (['solid' => __('Einfarbig'), 'gradient' => __('Verlauf'), 'image' => __('Bild')] as $value => $label)
                            <label @class(['cursor-pointer rounded-full px-4 py-2 text-sm border', 'border-indigo-600 bg-indigo-50' => $wallpaper_style === $value, 'border-gray-200' => $wallpaper_style !== $value])>
                                <input type="radio" wire:model.live="wallpaper_style" value="{{ $value }}" class="sr-only" />
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                    @if ($wallpaper_style === 'solid')
                        <div class="mt-4">
                            <x-input-label for="wallpaper_color" :value="__('Hintergrundfarbe')" />
                            <input type="color" wire:model.live.debounce.150ms="wallpaper_color" id="wallpaper_color" class="mt-1 h-10 w-20 rounded border border-gray-300" />
                        </div>
                    @endif
                    @if ($wallpaper_style === 'gradient')
                        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="wallpaper_gradient_from" :value="__('Farbe oben')" />
                                <input type="color" wire:model.live.debounce.150ms="wallpaper_gradient_from" id="wallpaper_gradient_from" class="mt-1 h-10 w-20 rounded border border-gray-300" />
                            </div>
                            <div>
                                <x-input-label for="wallpaper_gradient_to" :value="__('Farbe unten')" />
                                <input type="color" wire:model.live.debounce.150ms="wallpaper_gradient_to" id="wallpaper_gradient_to" class="mt-1 h-10 w-20 rounded border border-gray-300" />
                            </div>
                            <div class="sm:col-span-2">
                                <x-input-label for="wallpaper_gradient_angle" :value="__('Winkel (:deg°)', ['deg' => $wallpaper_gradient_angle])" />
                                <input type="range" wire:model.live="wallpaper_gradient_angle" id="wallpaper_gradient_angle" min="0" max="360" class="mt-1 w-full" />
                            </div>
                        </div>
                    @endif
                @endif

                @if ($activeSection === 'text')
                    <h2 class="text-lg font-semibold text-gray-900">{{ __('Text') }}</h2>
                    <div class="mt-4 space-y-4">
                        <div>
                            <x-input-label for="font_family" :value="__('Schriftart')" />
                            <select wire:model.live="font_family" id="font_family" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                @foreach (['figtree' => 'Figtree', 'inter' => 'Inter', 'playfair' => 'Playfair Display', 'space-mono' => 'Space Mono', 'dm-sans' => 'DM Sans'] as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex flex-wrap gap-6">
                            <div>
                                <x-input-label for="font_title_color" :value="__('Titelfarbe')" />
                                <input type="color" wire:model.live.debounce.150ms="font_title_color" id="font_title_color" class="mt-1 h-10 w-20 rounded border border-gray-300" />
                            </div>
                            <div>
                                <x-input-label for="font_text_color" :value="__('Textfarbe')" />
                                <input type="color" wire:model.live.debounce.150ms="font_text_color" id="font_text_color" class="mt-1 h-10 w-20 rounded border border-gray-300" />
                            </div>
                        </div>
                    </div>
                @endif

                @if ($activeSection === 'buttons')
                    <h2 class="text-lg font-semibold text-gray-900">{{ __('Buttons') }}</h2>
                    <div class="mt-4 space-y-6">
                        <div>
                            <p class="text-sm font-medium text-gray-700 mb-2">{{ __('Stil') }}</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach (['solid' => __('Solid'), 'glass' => __('Glass'), 'outline' => __('Outline')] as $value => $label)
                                    <label @class(['cursor-pointer rounded-lg px-3 py-2 text-sm border', 'border-indigo-600 bg-indigo-50' => $button_style === $value, 'border-gray-200' => $button_style !== $value])>
                                        <input type="radio" wire:model.live="button_style" value="{{ $value }}" class="sr-only" />{{ $label }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-700 mb-2">{{ __('Form') }}</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach (['square' => __('Square'), 'rounded' => __('Round'), 'pill' => __('Pill')] as $value => $label)
                                    <label @class(['cursor-pointer rounded-lg px-3 py-2 text-sm border', 'border-indigo-600 bg-indigo-50' => $button_shape === $value, 'border-gray-200' => $button_shape !== $value])>
                                        <input type="radio" wire:model.live="button_shape" value="{{ $value }}" class="sr-only" />{{ $label }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-700 mb-2">{{ __('Schatten') }}</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach (['none' => __('None'), 'soft' => __('Soft'), 'strong' => __('Strong'), 'hard' => __('Hard')] as $value => $label)
                                    <label @class(['cursor-pointer rounded-lg px-3 py-2 text-sm border', 'border-indigo-600 bg-indigo-50' => $button_shadow === $value, 'border-gray-200' => $button_shadow !== $value])>
                                        <input type="radio" wire:model.live="button_shadow" value="{{ $value }}" class="sr-only" />{{ $label }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-6">
                            <div>
                                <x-input-label for="button_color" :value="__('Buttonfarbe')" />
                                <input type="color" wire:model.live.debounce.150ms="button_color" id="button_color" class="mt-1 h-10 w-20 rounded border border-gray-300" />
                            </div>
                            <div>
                                <x-input-label for="button_text_color" :value="__('Buttontext')" />
                                <input type="color" wire:model.live.debounce.150ms="button_text_color" id="button_text_color" class="mt-1 h-10 w-20 rounded border border-gray-300" />
                            </div>
                        </div>
                    </div>
                @endif
                </form>

                @if ($activeSection === 'header' && $header_layout === 'banner')
                    <div class="mt-6 border-t pt-6">
                        <p class="text-sm font-medium text-gray-900">{{ __('Banner-Bild') }}</p>
                        @if ($profile->banner_image_path)
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($profile->banner_image_path) }}" alt="" class="mt-2 w-full max-h-40 object-cover rounded-lg" />
                        @endif
                        <form action="{{ route('design.banner.store') }}" method="post" enctype="multipart/form-data" class="mt-3 flex flex-wrap gap-2 items-end">
                            @csrf
                            <input type="file" name="banner" accept="image/*" required class="text-sm" />
                            <x-primary-button type="submit">{{ __('Hochladen') }}</x-primary-button>
                        </form>
                        @if ($profile->banner_image_path)
                            <form action="{{ route('design.banner.destroy') }}" method="post" class="mt-2">
                                @csrf
                                @method('DELETE')
                                <x-danger-button type="submit">{{ __('Banner entfernen') }}</x-danger-button>
                            </form>
                        @endif
                    </div>
                @endif

                @if ($activeSection === 'wallpaper' && $wallpaper_style === 'image')
                    <div class="mt-4 border-t pt-6">
                        @if ($profile->wallpaper_image_path)
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($profile->wallpaper_image_path) }}" alt="" class="w-full max-h-48 object-cover rounded-lg" />
                        @endif
                        <form action="{{ route('design.wallpaper.store') }}" method="post" enctype="multipart/form-data" class="mt-3 flex flex-wrap gap-2 items-end">
                            @csrf
                            <input type="file" name="wallpaper" accept="image/*" required class="text-sm" />
                            <x-primary-button type="submit">{{ __('Hochladen') }}</x-primary-button>
                        </form>
                        @if ($profile->wallpaper_image_path)
                            <form action="{{ route('design.wallpaper.destroy') }}" method="post" class="mt-2">
                                @csrf
                                @method('DELETE')
                                <x-danger-button type="submit">{{ __('Bild entfernen') }}</x-danger-button>
                            </form>
                        @endif
                        <x-input-error :messages="$errors->get('wallpaper_style')" class="mt-2" />
                    </div>
                @endif

                <div class="mt-8 flex flex-col items-end gap-3 border-t border-gray-100 pt-6">
                    @if ($errors->any())
                        <div class="w-full rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900" role="alert">
                            {{ __('Speichern fehlgeschlagen — bitte prüfe die markierten Felder in allen Design-Bereichen.') }}
                        </div>
                    @endif
                    <x-primary-button type="button" wire:click="save" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="save">{{ __('Design speichern') }}</span>
                        <span wire:loading wire:target="save">{{ __('Speichern…') }}</span>
                    </x-primary-button>
                </div>
            </div>

            {{-- Vorschau (live, gleiche Logik wie öffentliche Bio) --}}
            <div
                class="w-full max-w-[18.75rem] mx-auto xl:mx-0 xl:sticky xl:top-24 shrink-0"
                wire:loading.class="opacity-70"
                wire:target="theme_id, theme_filter, header_layout, wallpaper_style, wallpaper_color, wallpaper_gradient_from, wallpaper_gradient_to, wallpaper_gradient_angle, font_family, font_text_color, font_title_color, button_style, button_shape, button_shadow, button_color, button_text_color"
            >
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-2 text-center xl:text-left">{{ __('Live-Vorschau') }}</p>
                @include('livewire.partials.design-preview', [
                    'previewProfile' => $previewProfile,
                    'presentation' => $previewPresentation,
                    'previewKey' => $previewKey,
                ])
            </div>
        </div>
    </div>
</div>
