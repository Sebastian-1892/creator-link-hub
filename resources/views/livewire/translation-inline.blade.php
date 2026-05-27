<div>
    @if ($open)
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/40" wire:click.self="close">
            <div class="w-full max-w-lg rounded-xl bg-white shadow-xl border border-gray-200 p-6" wire:click.stop>
                <h2 class="text-lg font-semibold text-gray-900">{{ __('Text bearbeiten') }}</h2>
                <p class="mt-1 text-xs text-gray-500 font-mono">{{ $translationKey }} · {{ strtoupper($locale) }}</p>

                @if ($format === \App\Models\TranslationString::FORMAT_MARKDOWN)
                    <textarea wire:model="value" rows="10" class="mt-4 w-full rounded-md border-gray-300 shadow-sm text-sm"></textarea>
                @else
                    <textarea wire:model="value" rows="4" class="mt-4 w-full rounded-md border-gray-300 shadow-sm text-sm"></textarea>
                @endif

                <div class="mt-4 flex flex-wrap gap-2">
                    <button type="button" wire:click="save" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                        {{ __('Speichern') }}
                    </button>
                    <button type="button" wire:click="revert" class="rounded-md border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                        {{ __('Zurücksetzen') }}
                    </button>
                    <button type="button" wire:click="close" class="rounded-md px-4 py-2 text-sm text-gray-500 hover:text-gray-800">
                        {{ __('Abbrechen') }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
