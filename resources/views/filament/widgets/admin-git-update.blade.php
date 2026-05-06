@php
    use App\Services\ApplicationUpdateService;
    $updates = app(ApplicationUpdateService::class);
    $repoPath = $updates->repositoryPath();
    $manifestUrl = config('creator.update_manifest_url');
@endphp

<x-filament-widgets::widget class="fi-admin-git-update" wire:init="bootCheck">
    <x-filament::section
        :heading="__('filament_git_update.title')"
        :description="__('filament_git_update.intro')"
    >
        <div class="space-y-6 text-sm">
            <div>
                <h3 class="text-sm font-semibold text-gray-950 dark:text-white">
                    {{ __('filament_git_update.manifest_section') }}
                </h3>
                <div class="mt-3 space-y-3 rounded-lg bg-gray-50 p-3 dark:bg-white/5">
                    <div>
                        <p class="m-0 text-xs font-medium text-gray-500 dark:text-gray-400">
                            {{ __('filament_git_update.manifest_url_label') }}
                        </p>
                        <p class="mt-1 break-all font-mono text-xs text-gray-950 dark:text-white">
                            {{ $manifestUrl }}
                        </p>
                    </div>
                    <dl class="m-0 grid gap-2 sm:grid-cols-2">
                        <div>
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('filament_git_update.manifest_installed') }}</dt>
                            <dd class="mt-0.5 font-mono font-semibold text-gray-950 dark:text-white">
                                {{ config('creator.installed_version') }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('filament_git_update.manifest_latest') }}</dt>
                            <dd class="mt-0.5 font-mono font-semibold text-gray-950 dark:text-white">
                                {{ $manifest['latest_version'] ?? '—' }}
                            </dd>
                        </div>
                        @if (isset($manifest['min_php_version']) && $manifest['min_php_version'])
                            <div class="sm:col-span-2">
                                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('filament_git_update.manifest_min_php') }}</dt>
                                <dd class="mt-0.5 font-mono text-gray-950 dark:text-white">{{ $manifest['min_php_version'] }}</dd>
                            </div>
                        @endif
                    </dl>

                    @if ($manifest && ($manifest['release_update_available'] ?? false))
                        <x-filament::badge color="warning" size="lg">
                            {{ __('filament_git_update.manifest_update_available') }}
                        </x-filament::badge>
                    @elseif ($manifest)
                        <x-filament::badge color="success">
                            {{ __('filament_git_update.manifest_up_to_date') }}
                        </x-filament::badge>
                    @else
                        <p class="m-0 text-xs text-gray-500 dark:text-gray-400">{{ __('filament_git_update.manifest_not_loaded') }}</p>
                    @endif

                    @if ($manifest && ($manifest['changelog_url'] ?? null))
                        <div class="flex flex-wrap gap-2">
                            <x-filament::button
                                color="gray"
                                size="sm"
                                tag="a"
                                :href="$manifest['changelog_url']"
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                {{ __('filament_git_update.manifest_open_changelog') }}
                            </x-filament::button>
                            @if ($manifest['download_url'] ?? null)
                                <x-filament::button
                                    color="gray"
                                    size="sm"
                                    tag="a"
                                    :href="$manifest['download_url']"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                >
                                    {{ __('filament_git_update.manifest_open_download') }}
                                </x-filament::button>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

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

            @if ($lastError !== '')
                <pre class="max-h-40 overflow-auto rounded-md bg-gray-950/5 p-2 text-xs text-red-600 dark:bg-white/5 dark:text-red-400">{{ $lastError }}</pre>
            @endif
        </div>

        <x-slot name="footer">
            <div class="flex flex-wrap items-center gap-2">
                <x-filament::button
                    color="gray"
                    size="sm"
                    type="button"
                    wire:click="checkForUpdates"
                    wire:loading.attr="disabled"
                    wire:target="checkForUpdates,applyUpdateFromDashboard,bootCheck"
                >
                    <span wire:loading.remove wire:target="checkForUpdates,applyUpdateFromDashboard,bootCheck">{{ __('filament_git_update.check') }}</span>
                    <span wire:loading wire:target="checkForUpdates,bootCheck">{{ __('filament_git_update.checking') }}</span>
                </x-filament::button>

                @if ($updates->isUpdateScriptAvailable())
                    <x-filament::button
                        color="primary"
                        size="sm"
                        type="button"
                        wire:click="applyUpdateFromDashboard"
                        wire:confirm="{{ __('filament_git_update.apply_confirm') }}"
                        wire:loading.attr="disabled"
                        wire:target="checkForUpdates,applyUpdateFromDashboard,bootCheck"
                    >
                        <span wire:loading.remove wire:target="applyUpdateFromDashboard">{{ __('filament_git_update.apply') }}</span>
                        <span wire:loading wire:target="applyUpdateFromDashboard">{{ __('filament_git_update.applying') }}</span>
                    </x-filament::button>
                @endif
            </div>
        </x-slot>
    </x-filament::section>
</x-filament-widgets::widget>
