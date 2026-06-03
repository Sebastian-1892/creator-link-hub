<div class="py-10">
    @if ($saveNotice)
        <div
            class="fixed top-20 left-0 right-0 z-50 flex justify-center px-4 pointer-events-none"
            role="status"
            aria-live="polite"
        >
            <div class="pointer-events-auto flex max-w-lg items-center gap-3 rounded-xl border border-green-600 bg-green-600 px-5 py-4 text-white shadow-xl ring-2 ring-green-200">
                <svg class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                <p class="text-base font-semibold">{{ $saveNotice }}</p>
                <button type="button" wire:click="dismissSaveNotice" class="ml-1 rounded-md p-1 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-white" aria-label="{{ __('Schließen') }}">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
        </div>
    @endif

    <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <h1 class="text-2xl font-semibold text-gray-900">{{ __('Bio-Seite bearbeiten') }}</h1>

        @if ($saveNotice)
            <div
                id="bio-save-notice"
                class="rounded-xl border-2 border-green-500 bg-green-50 px-5 py-4 text-green-900 shadow-sm flex items-start justify-between gap-4"
                role="status"
            >
                <div class="flex items-start gap-3">
                    <svg class="h-6 w-6 shrink-0 text-green-600 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    <p class="text-base font-semibold">{{ $saveNotice }}</p>
                </div>
                <button type="button" wire:click="dismissSaveNotice" class="text-green-800 hover:text-green-950 text-xl leading-none px-1" aria-label="{{ __('Schließen') }}">×</button>
            </div>
        @endif

        <div class="bg-white shadow sm:rounded-lg overflow-hidden">
            @if (session('avatar_notice'))
                <div class="border-b border-green-200 bg-green-50 px-6 py-3 text-green-900" role="status">
                    <p class="text-sm font-semibold">{{ session('avatar_notice') }}</p>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,2fr)_minmax(220px,1fr)]">
                {{-- Profilbild (rechts auf Desktop, zuerst auf Mobile) --}}
                <div class="order-1 lg:order-none lg:col-start-2 lg:row-start-1 p-6 bg-gray-50/80 lg:bg-gray-50 border-b lg:border-b-0 lg:border-l border-gray-100">
                    <h2 class="text-sm font-semibold text-gray-900 text-center lg:text-left">{{ __('Profilbild') }}</h2>
                    <div class="mt-4 flex flex-col items-center">
                        @if ($profile->avatar_path)
                            <img
                                src="{{ \Illuminate\Support\Facades\Storage::url($profile->avatar_path) }}"
                                alt=""
                                class="h-28 w-28 rounded-full object-cover ring-4 ring-white shadow-md"
                                width="112"
                                height="112"
                            />
                        @else
                            <div class="flex h-28 w-28 items-center justify-center rounded-full bg-gray-200 ring-4 ring-white shadow-inner" aria-hidden="true">
                                <svg class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                </svg>
                            </div>
                        @endif

                        <form action="{{ route('bio.avatar.store') }}" method="post" enctype="multipart/form-data" class="mt-4 w-full max-w-xs space-y-3">
                            @csrf
                            <div>
                                <x-input-label for="avatar" :value="__('Neues Profilbild')" class="sr-only" />
                                <input
                                    id="avatar"
                                    name="avatar"
                                    type="file"
                                    required
                                    accept=".jpg,.jpeg,.png,.gif,.webp,image/jpeg,image/png,image/gif,image/webp"
                                    class="block w-full text-xs text-gray-500 file:mr-3 file:rounded-md file:border-0 file:bg-indigo-50 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100"
                                />
                                <p class="mt-2 text-center text-xs text-gray-500 lg:text-left">
                                    {{ __('JPG, PNG, GIF oder WebP (bis 8 MB). Wird automatisch verkleinert.') }}
                                </p>
                                <x-input-error :messages="$errors->get('avatar')" class="mt-2" />
                            </div>
                            <x-primary-button type="submit" class="w-full justify-center text-sm">
                                {{ __('Profilbild hochladen') }}
                            </x-primary-button>
                        </form>
                    </div>
                </div>

                <form wire:submit="save" class="contents">
                    {{-- Stammdaten (links auf Desktop) --}}
                    <div class="order-2 lg:order-none lg:col-start-1 lg:row-start-1 p-6 space-y-5 lg:border-r border-gray-100">
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
                            <textarea wire:model="bio" id="bio" rows="3" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                            <x-input-error :messages="$errors->get('bio')" class="mt-2" />
                        </div>
                    </div>

                    <div class="order-3 lg:order-none lg:col-span-2 px-6 py-4 border-t border-gray-100 bg-indigo-50/50">
                        <p class="text-sm text-indigo-900">
                            {{ __('Theme, Hintergrund, Schrift und Buttons passt du im') }}
                            <a href="{{ route('design.edit') }}" class="font-semibold underline">{{ __('Design-Tab') }}</a>
                            {{ __('an.') }}
                        </p>
                    </div>

                    {{-- Footer --}}
                    <div class="order-4 lg:order-none lg:col-span-2 flex flex-col gap-4 border-t border-gray-100 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
                        <div class="space-y-3">
                            <div class="flex items-center gap-2">
                                <input wire:model.boolean="is_published" id="is_published" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                                <x-input-label for="is_published" :value="__('Öffentlich veröffentlichen')" class="!mb-0" />
                            </div>
                            <div class="flex items-start gap-2">
                                <input
                                    wire:model.boolean="show_platform_branding"
                                    id="show_platform_branding"
                                    type="checkbox"
                                    class="mt-0.5 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                    @disabled(! $canControlPlatformBranding)
                                />
                                <div>
                                    <x-input-label for="show_platform_branding" :value="__('„Built with …“-Hinweis anzeigen')" class="!mb-0" />
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        @if ($canControlPlatformBranding)
                                            {{ __('Zeigt unten auf deiner Bio-Seite „Erstellt mit“ mit Link zum Markennamen des Betreibers.') }}
                                        @else
                                            {{ __('Im Free-Plan ist dieser Hinweis vorgesehen und kann nicht ausgeblendet werden.') }}
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-end gap-3">
                            <p wire:loading wire:target="save" class="text-sm text-indigo-600 font-medium">{{ __('Wird gespeichert…') }}</p>
                            <x-primary-button type="submit" wire:loading.attr="disabled" wire:target="save">
                                <span wire:loading.remove wire:target="save">{{ __('Speichern') }}</span>
                                <span wire:loading wire:target="save">{{ __('Speichern…') }}</span>
                            </x-primary-button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
