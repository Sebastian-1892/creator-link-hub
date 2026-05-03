<?php

namespace App\Filament\Support;

use Filament\Actions\Action;
use Illuminate\Support\Facades\Auth;

final class FilamentLocaleUserMenu
{
    /**
     * @return array<int, Action>
     */
    public static function menuActions(): array
    {
        $locales = config('creator.filament_locales', []);
        if ($locales === []) {
            return [];
        }

        $sort = 50;
        $actions = [];

        foreach ($locales as $code => $meta) {
            $native = is_array($meta) ? (string) ($meta['native'] ?? $code) : (string) $meta;
            $flag = is_array($meta) && isset($meta['flag']) ? (string) $meta['flag'] : self::defaultFlag((string) $code);

            $actions[] = Action::make('filament_locale_'.$code)
                ->label(trim($flag.' '.$native))
                ->url(url('/set-filament-locale/'.$code))
                ->sort($sort++)
                ->visible(fn (): bool => (bool) Auth::user()?->is_admin)
                ->disabled(fn (): bool => self::isCurrentLocale((string) $code));
        }

        return $actions;
    }

    private static function defaultFlag(string $code): string
    {
        return match ($code) {
            'en' => '🇬🇧',
            'de' => '🇩🇪',
            'fr' => '🇫🇷',
            'it' => '🇮🇹',
            default => '🌐',
        };
    }

    private static function isCurrentLocale(string $code): bool
    {
        $user = Auth::user();
        if (! $user || ! $user->is_admin) {
            return false;
        }

        $current = is_string($user->filament_locale) ? $user->filament_locale : app()->getLocale();

        return $current === $code;
    }
}
