<?php

namespace App\Filament\Widgets;

use Filament\Facades\Filament;
use Filament\Widgets\Widget;

class AdminWelcomeInfoWidget extends Widget
{
    private const STORAGE_VERSION = 'v1';

    protected static ?int $sort = 5;

    protected static bool $isLazy = false;

    /**
     * @var view-string
     */
    protected string $view = 'filament.widgets.admin-welcome-info';

    protected int|string|array $columnSpan = [
        'default' => 2,
        'lg' => 1,
    ];

    protected int|string|array $columnStart = [
        'default' => 1,
        'lg' => 2,
    ];

    public static function canView(): bool
    {
        $user = Filament::auth()->user();

        return (bool) ($user?->is_admin)
            && request()->routeIs('filament.admin.pages.dashboard');
    }

    /**
     * @return array<string, string>
     */
    protected function getViewData(): array
    {
        $userId = auth()->id();

        return [
            'storageKey' => 'clh_filament_welcome_'.($userId ?? 0).'_'.self::STORAGE_VERSION,
        ];
    }
}
