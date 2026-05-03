@php
    $sections = ['dashboard', 'profiles', 'users', 'workspaces'];
@endphp

<x-filament-widgets::widget
    class="fi-admin-welcome-info"
    x-data="{
        storageKey: @js($storageKey),
        dismissed: false,
        init() {
            try {
                this.dismissed = localStorage.getItem(this.storageKey) === '1';
            } catch (e) {
                this.dismissed = false;
            }
        },
        dismiss() {
            try {
                localStorage.setItem(this.storageKey, '1');
            } catch (e) {}
            this.dismissed = true;
        },
    }"
    x-show="! dismissed"
    x-cloak
    wire:key="admin-welcome-{{ $storageKey }}"
>
    <x-filament::section
        :heading="__('filament_welcome.title')"
        :description="__('filament_welcome.intro')"
    >
        <div class="space-y-0 text-sm">
            <ul class="m-0 list-none space-y-4 p-0">
                @foreach ($sections as $key)
                    <li class="border-b border-gray-200 pb-3 last:border-0 last:pb-0 dark:border-white/10">
                        <p class="m-0 font-semibold text-gray-950 dark:text-white">
                            {{ __('filament_welcome.sections.'.$key.'.title') }}
                        </p>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            {{ __('filament_welcome.sections.'.$key.'.body') }}
                        </p>
                    </li>
                @endforeach
            </ul>

            <div class="mt-6 rounded-lg bg-amber-500/10 p-4 ring-1 ring-amber-500/20 dark:bg-amber-500/5 dark:ring-amber-400/20">
                <p class="m-0 font-semibold text-gray-950 dark:text-white">
                    {{ __('filament_welcome.billing_title') }}
                </p>
                <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">
                    {{ __('filament_welcome.billing_stripe') }}
                </p>
                <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">
                    {{ __('filament_welcome.billing_plans') }}
                </p>
            </div>
        </div>

        <x-slot name="footer">
            <div class="flex flex-wrap gap-2">
                <x-filament::button color="gray" size="sm" type="button" x-on:click="dismiss()">
                    {{ __('filament_welcome.dismiss') }}
                </x-filament::button>
            </div>
        </x-slot>
    </x-filament::section>
</x-filament-widgets::widget>

