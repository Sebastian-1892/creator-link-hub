<div class="py-10">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <h1 class="text-2xl font-semibold text-gray-900">{{ __('Bio-Seite bearbeiten') }}</h1>

        @if (session('status'))
            <div class="rounded-md bg-green-50 p-4 text-sm text-green-800">{{ session('status') }}</div>
        @endif

        <form wire:submit="save" class="bg-white shadow sm:rounded-lg p-6 space-y-6">
            <div>
                <x-input-label for="avatar" :value="__('Profilbild')" />
                <input wire:model="avatar" id="avatar" type="file" accept="image/*" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:rounded-md file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100" />
                <x-input-error :messages="$errors->get('avatar')" class="mt-2" />
                @if ($profile->avatar_path)
                    <p class="mt-2 text-xs text-gray-500">{{ __('Aktuell hochgeladen') }}</p>
                @endif
            </div>

            <div>
                <x-input-label for="display_name" :value="__('Anzeigename')" />
                <x-text-input wire:model="display_name" id="display_name" class="block mt-1 w-full" />
                <x-input-error :messages="$errors->get('display_name')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="slug" :value="__('URL-Pfad')" />
                <div class="mt-1 flex rounded-md shadow-sm">
                    <span class="inline-flex items-center rounded-l-md border border-r-0 border-gray-300 bg-gray-50 px-3 text-sm text-gray-500">{{ url('/p') }}/</span>
                    <x-text-input wire:model="slug" id="slug" class="rounded-l-none block w-full" />
                </div>
                <x-input-error :messages="$errors->get('slug')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="bio" :value="__('Bio')" />
                <textarea wire:model="bio" id="bio" rows="4" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm"></textarea>
                <x-input-error :messages="$errors->get('bio')" class="mt-2" />
            </div>

            <div>
                <x-input-label :value="__('Profil-Vorlage')" />
                <p class="mt-1 text-xs text-gray-500">{{ __('Wähle Layout und Farben — Vorschau mit Avatar und drei Buttons.') }}</p>
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
                <div class="mt-4 max-h-[32rem] overflow-y-auto rounded-lg border border-gray-200 bg-gray-50/50 p-3" role="radiogroup" aria-label="{{ __('Profil-Vorlage') }}">
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        <label @class([
                            'flex cursor-pointer flex-col rounded-xl border-2 bg-white p-3 shadow-sm transition',
                            'border-indigo-600 ring-2 ring-indigo-100' => blank($theme_id),
                            'border-gray-200 hover:border-gray-300' => filled($theme_id),
                        ])>
                            <input type="radio" wire:model.live="theme_id" value="" class="sr-only" />
                            <span class="text-sm font-medium text-gray-900">{{ __('Standard (Theme wählen)') }}</span>
                            <span class="mt-1 text-xs text-gray-500">{{ __('Später festlegen.') }}</span>
                        </label>
                        @foreach ($themes as $theme)
                            @php
                                $v = is_array($theme->variables) ? $theme->variables : [];
                                $bg = $v['bg'] ?? '#e5e7eb';
                                $text = $v['text'] ?? '#111827';
                                $accent = $v['accent'] ?? '#6366f1';
                                $card = $v['card'] ?? '#f3f4f6';
                                $btnRadius = match ($theme->button_style ?? 'pill') {
                                    'square' => '4px',
                                    'rounded', 'glass', 'shadow' => '12px',
                                    default => '9999px',
                                };
                                $glass = ($theme->button_style ?? '') === 'glass';
                            @endphp
                            <label @class([
                                'flex cursor-pointer flex-col rounded-xl border-2 bg-white p-3 shadow-sm transition',
                                'border-indigo-600 ring-2 ring-indigo-100' => filled($theme_id) && (int) $theme_id === (int) $theme->id,
                                'border-gray-200 hover:border-gray-300' => blank($theme_id) || (int) $theme_id !== (int) $theme->id,
                            ])>
                                <input type="radio" wire:model.live="theme_id" value="{{ $theme->id }}" class="sr-only" />
                                <span class="text-xs font-semibold text-gray-900 truncate">{{ $theme->name }}</span>
                                <div class="mt-2 rounded-lg border border-black/10 overflow-hidden" style="background: {{ $bg }};">
                                    <div class="p-2 flex flex-col items-center gap-1.5">
                                        <div class="h-7 w-7 rounded-full border-2 shrink-0" style="border-color: {{ $accent }};"></div>
                                        <div class="h-1 w-16 rounded-full opacity-40" style="background: {{ $text }};"></div>
                                        @foreach (range(1, 3) as $i)
                                            <div
                                                class="w-full h-6 text-[9px] font-bold flex items-center justify-center px-1 truncate"
                                                style="
                                                    background: {{ $glass ? 'rgba(255,255,255,0.12)' : $card }};
                                                    color: {{ $accent }};
                                                    border-radius: {{ $btnRadius }};
                                                    border: 1px solid {{ $glass ? 'rgba(255,255,255,0.2)' : 'rgba(0,0,0,0.06)' }};
                                                    {{ $glass ? 'backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px);' : '' }}
                                                "
                                            >•••</div>
                                        @endforeach
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
                <x-input-error :messages="$errors->get('theme_id')" class="mt-2" />
            </div>

            <div class="flex items-center gap-2">
                <input wire:model.boolean="is_published" id="is_published" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm" />
                <x-input-label for="is_published" :value="__('Öffentlich veröffentlichen')" />
            </div>

            <div class="flex justify-end">
                <x-primary-button type="submit">{{ __('Speichern') }}</x-primary-button>
            </div>
        </form>

        <div class="text-sm text-gray-600">
            {{ __('Öffentliche Vorschau') }}:
            <a class="text-indigo-600 underline" href="{{ route('public.profile', $profile->slug) }}" target="_blank" wire:key="preview-{{ $profile->slug }}">{{ route('public.profile', $profile->slug) }}</a>
        </div>
    </div>
</div>
