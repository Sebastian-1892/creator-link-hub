<div class="py-10">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
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
                <x-input-label for="theme_id" :value="__('Theme')" />
                <select wire:model.number="theme_id" id="theme_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    <option value="">{{ __('— Theme wählen —') }}</option>
                    @foreach ($themes as $theme)
                        <option value="{{ $theme->id }}">{{ $theme->name }}</option>
                    @endforeach
                </select>
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
