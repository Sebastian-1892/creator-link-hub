@php
    use App\Services\ApplicationUpdateService;
    $updates = app(ApplicationUpdateService::class);
    $repoPath = $updates->repositoryPath();
@endphp

<x-filament-widgets::widget class="fi-admin-git-update">
    <x-filament::section
        :heading="__('filament_git_update.title')"
        :description="__('filament_git_update.intro')"
    >
        <div class="space-y-6 text-sm">
            <div>
                <h3 class="text-sm font-semibold text-gray-950 dark:text-white">
                    {{ __('filament_git_update.shell_section') }}
                </h3>
                <div class="mt-3 space-y-3 rounded-lg bg-gray-50 p-3 dark:bg-white/5">
                    <div>
                        <p class="m-0 text-xs font-medium text-gray-500 dark:text-gray-400">
                            {{ __('filament_git_update.path_label') }}
                        </p>
                        <p class="mt-1 break-all font-mono text-xs text-gray-950 dark:text-white">
                            {{ $repoPath }}
                        </p>
                    </div>
                    <p class="m-0 text-xs text-gray-600 dark:text-gray-300">
                        {{ __('filament_git_update.shell_intro') }}
                    </p>
                    @if (! $updates->isUpdateScriptAvailable())
                        <x-filament::badge color="danger">
                            {{ __('filament_git_update.script_missing') }}
                        </x-filament::badge>
                    @else
                        <x-filament::badge color="success">
                            {{ __('filament_git_update.script_ready') }}
                        </x-filament::badge>
                    @endif
                </div>
            </div>
        </div>

        <x-slot name="footer">
            <div class="flex flex-wrap items-center gap-2">
                @if ($updates->isUpdateScriptAvailable())
                    <x-filament::button
                        color="primary"
                        size="sm"
                        type="button"
                        wire:click="applyUpdateFromDashboard"
                        wire:confirm="{{ __('filament_git_update.apply_confirm') }}"
                        wire:loading.attr="disabled"
                        wire:target="applyUpdateFromDashboard"
                    >
                        <span wire:loading.remove wire:target="applyUpdateFromDashboard">{{ __('filament_git_update.apply') }}</span>
                        <span wire:loading wire:target="applyUpdateFromDashboard">{{ __('filament_git_update.applying') }}</span>
                    </x-filament::button>
                @endif
            </div>
        </x-slot>
    </x-filament::section>
</x-filament-widgets::widget>
