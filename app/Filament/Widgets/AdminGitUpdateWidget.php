<?php

namespace App\Filament\Widgets;

use App\Services\ClhUpdateManifestService;
use App\Services\GitDeploymentUpdateService;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use Illuminate\Support\Arr;

class AdminGitUpdateWidget extends Widget
{
    protected static ?int $sort = 6;

    protected static bool $isLazy = false;

    /**
     * @var view-string
     */
    protected string $view = 'filament.widgets.admin-git-update';

    /** @var array<string, mixed>|null */
    public ?array $manifest = null;

    /** @var array<string, mixed>|null */
    public ?array $gitCheck = null;

    public string $lastError = '';

    public bool $busy = false;

    public static function canView(): bool
    {
        $user = Filament::auth()->user();

        return (bool) ($user?->is_admin)
            && request()->routeIs('filament.admin.pages.dashboard');
    }

    /**
     * Beim Dashboard-Laden: Release-Manifest (pCloud) prüfen — kein Git-Fetch (schneller).
     */
    public function bootCheck(ClhUpdateManifestService $manifests): void
    {
        abort_unless(Filament::auth()->user()?->is_admin, 403);

        if ($this->busy) {
            return;
        }

        $this->busy = true;
        $this->lastError = '';
        set_time_limit(0);

        try {
            $m = $manifests->refresh();
            if (! $m['ok']) {
                $this->manifest = null;
                $this->lastError = trim((string) (Arr::get($m, 'details') ?: Arr::get($m, 'error', 'error')));

                return;
            }

            $this->manifest = $m;

            if ($m['release_update_available'] ?? false) {
                Notification::make()
                    ->title(__('filament_git_update.manifest_update_available'))
                    ->warning()
                    ->send();
            }
        } finally {
            $this->busy = false;
        }
    }

    public function checkForUpdates(ClhUpdateManifestService $manifests, GitDeploymentUpdateService $git): void
    {
        abort_unless(Filament::auth()->user()?->is_admin, 403);

        if ($this->busy) {
            return;
        }

        $this->busy = true;
        $this->lastError = '';
        set_time_limit(0);

        try {
            $m = $manifests->refresh();
            if (! $m['ok']) {
                $this->manifest = null;
                $this->lastError = trim((string) (Arr::get($m, 'details') ?: Arr::get($m, 'error', 'error')));
                Notification::make()
                    ->title(__('filament_git_update.manifest_check_failed'))
                    ->body($this->lastError !== '' ? $this->lastError : null)
                    ->danger()
                    ->send();
            } else {
                $this->manifest = $m;
                if ($m['release_update_available'] ?? false) {
                    Notification::make()
                        ->title(__('filament_git_update.manifest_update_available'))
                        ->success()
                        ->send();
                } else {
                    Notification::make()
                        ->title(__('filament_git_update.manifest_up_to_date'))
                        ->success()
                        ->send();
                }
            }

            if ($git->isGitDeployment()) {
                $this->runGitCheck($git, withNotifications: true);
            } else {
                $this->gitCheck = null;
            }
        } finally {
            $this->busy = false;
        }
    }

    public function applyUpdateFromDashboard(GitDeploymentUpdateService $git): void
    {
        $this->runApply($git, false);
    }

    public function applyUpdateIgnoringDirtyTree(GitDeploymentUpdateService $git): void
    {
        $this->runApply($git, true);
    }

    private function runGitCheck(GitDeploymentUpdateService $git, bool $withNotifications): void
    {
        if (! $git->isGitDeployment()) {
            $this->gitCheck = null;

            return;
        }

        $result = $git->checkForUpdates();

        if (! $result['ok']) {
            $this->gitCheck = null;
            $msg = trim((string) (Arr::get($result, 'details') ?: Arr::get($result, 'error', 'error')));
            if ($withNotifications) {
                Notification::make()
                    ->title(__('filament_git_update.git_check_failed'))
                    ->body($msg !== '' ? $msg : null)
                    ->danger()
                    ->send();
            }

            return;
        }

        $this->gitCheck = $result;

        if ($withNotifications) {
            if ($result['update_available']) {
                Notification::make()
                    ->title(__('filament_git_update.git_update_available'))
                    ->success()
                    ->send();
            } else {
                Notification::make()
                    ->title(__('filament_git_update.git_up_to_date'))
                    ->success()
                    ->send();
            }
        }
    }

    private function runApply(GitDeploymentUpdateService $git, bool $forceDirty): void
    {
        abort_unless(Filament::auth()->user()?->is_admin, 403);

        if ($this->busy) {
            return;
        }

        $this->busy = true;
        set_time_limit(0);

        try {
            $result = $git->runUpdateScript($forceDirty);
            $body = $this->truncateOutput((string) ($result['output'] ?? ''));

            if ($result['ok']) {
                Notification::make()
                    ->title(__('filament_git_update.success_title'))
                    ->success()
                    ->body($body !== '' ? $body : null)
                    ->persistent()
                    ->send();
            } else {
                Notification::make()
                    ->title(__('filament_git_update.failure_title'))
                    ->danger()
                    ->body($body !== '' ? $body : null)
                    ->persistent()
                    ->send();
            }

            $this->syncManifestQuietly(app(ClhUpdateManifestService::class));
            $this->runGitCheck($git, withNotifications: false);
        } finally {
            $this->busy = false;
        }
    }

    private function syncManifestQuietly(ClhUpdateManifestService $manifests): void
    {
        $m = $manifests->refresh();
        if ($m['ok']) {
            $this->manifest = $m;
        }
    }

    private function truncateOutput(string $output): string
    {
        $max = 3500;

        if (strlen($output) <= $max) {
            return $output;
        }

        return "…\n".substr($output, -$max);
    }
}
