<?php

use App\Models\Profile;
use App\Services\SlugService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component
{
    #[Validate('required|string|max:120')]
    public string $display_name = '';

    #[Validate('required|string|max:64')]
    public string $slug = '';

    public string $goal = '';

    public function mount(): void
    {
        $profile = auth()->user()?->currentWorkspace()?->profile;

        if (! $profile) {
            abort(404);
        }

        $this->display_name = $profile->display_name;
        $this->slug = $profile->slug;
    }

    public function save(SlugService $slugService): void
    {
        $profile = auth()->user()->currentWorkspace()->profile;

        $this->validate([
            'display_name' => 'required|string|max:120',
            'slug' => [
                'required',
                'string',
                'max:64',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                \Illuminate\Validation\Rule::unique('profiles', 'slug')->ignore($profile->id),
                function (string $attribute, mixed $value, \Closure $fail) use ($slugService): void {
                    if ($slugService->isReserved((string) $value)) {
                        $fail(__('Diese URL ist reserviert.'));
                    }
                },
            ],
            'goal' => 'nullable|string|max:255',
        ]);

        $profile->display_name = $this->display_name;
        $profile->slug = strtolower($this->slug);
        $profile->save();

        auth()->user()->forceFill([
            'onboarding_completed_at' => now(),
        ])->save();

        $this->redirect(route('dashboard'), navigate: true);
    }
}; ?>

<div class="py-10">
    <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
        <h1 class="text-2xl font-semibold text-gray-900">{{ __('Willkommen bei Creator Link Hub') }}</h1>
        <p class="mt-2 text-gray-600">{{ __('Wie soll deine öffentliche Seite heißen und unter welcher URL erreichbar sein?') }}</p>

        <form wire:submit="save" class="mt-8 space-y-6 bg-white p-6 shadow sm:rounded-lg">
            <div>
                <x-input-label for="display_name" :value="__('Anzeigename')" />
                <x-text-input wire:model="display_name" id="display_name" class="block mt-1 w-full" type="text" required />
                <x-input-error :messages="$errors->get('display_name')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="slug" :value="__('URL-Pfad (Slug)')" />
                <div class="mt-1 flex rounded-md shadow-sm">
                    <span class="inline-flex items-center rounded-l-md border border-r-0 border-gray-300 bg-gray-50 px-3 text-sm text-gray-500">{{ url('/p') }}/</span>
                    <x-text-input wire:model="slug" id="slug" class="rounded-l-none block w-full" type="text" required />
                </div>
                <x-input-error :messages="$errors->get('slug')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="goal" :value="__('Dein Ziel (optional)')" />
                <x-text-input wire:model="goal" id="goal" class="block mt-1 w-full" type="text" />
            </div>
            <div class="flex justify-end">
                <x-primary-button type="submit">{{ __('Weiter zum Dashboard') }}</x-primary-button>
            </div>
        </form>
    </div>
</div>
