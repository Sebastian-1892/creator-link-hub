<?php

namespace App\Filament\Widgets;

use App\Services\ApplicationUpdateService;
use App\Services\ClhUpdateManifestService;
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

    public string $lastError = '';

    public bool $busy = false;

    public static function canView(): bool
    {
        $user = Filament::auth()->user();

        return (bool) ($user?->is_admin)
            && request()->routeIs('filament.admin.pages.dashboard');
    }

    /**
     * Beim Dashboard-Laden: Release-Manifest (pCloud) prüfen.
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

    public function checkForUpdates(ClhUpdateManifestService $manifests): void
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
        } finally {
            $this->busy = false;
        }
    }

    public function applyUpdateFromDashboard(ApplicationUpdateService $updates): void
    {
        $this->runApply($updates);
    }

    private function runApply(ApplicationUpdateService $updates): void
    {
        abort_unless(Filament::auth()->user()?->is_admin, 403);

        if ($this->busy) {
            return;
        }

        $this->busy = true;
        set_time_limit(0);

        try {
            $result = $updates->runUpdateScript();
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
