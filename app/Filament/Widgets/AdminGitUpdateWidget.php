<?php

namespace App\Filament\Widgets;

use App\Services\ApplicationUpdateService;
use App\Support\UpdateScriptOutputFormatter;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;

class AdminGitUpdateWidget extends Widget
{
    protected static ?int $sort = 6;

    protected static bool $isLazy = false;

    /**
     * @var view-string
     */
    protected string $view = 'filament.widgets.admin-git-update';

    public bool $busy = false;

    public static function canView(): bool
    {
        $user = Filament::auth()->user();

        return (bool) ($user?->is_admin)
            && request()->routeIs('filament.admin.pages.dashboard');
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
            $raw = (string) ($result['output'] ?? '');
            $body = UpdateScriptOutputFormatter::forNotification($raw, ! $result['ok']);
            $hint = __('filament_git_update.output_hint');
            $fullBody = $body !== ''
                ? trim($body."\n\n".$hint)
                : ($result['ok'] ? '' : $hint);

            if ($result['ok']) {
                Notification::make()
                    ->title(__('filament_git_update.success_title'))
                    ->success()
                    ->body($fullBody !== '' ? $fullBody : null)
                    ->persistent()
                    ->send();
            } else {
                Notification::make()
                    ->title(__('filament_git_update.failure_title'))
                    ->danger()
                    ->body($fullBody !== '' ? $fullBody : null)
                    ->persistent()
                    ->send();
            }
        } finally {
            $this->busy = false;
        }
    }
}
