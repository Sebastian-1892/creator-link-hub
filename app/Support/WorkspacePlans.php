<?php

namespace App\Support;

class WorkspacePlans
{
    /**
     * @return list<string>
     */
    public static function keys(): array
    {
        return array_keys(config('creator.plans', []));
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];
        foreach (self::keys() as $key) {
            $options[$key] = self::label($key);
        }

        return $options;
    }

    public static function label(string $plan): string
    {
        return match ($plan) {
            'free' => __('admin_settings.workspace.plan_free'),
            'starter' => __('admin_settings.workspace.plan_starter'),
            'pro' => __('admin_settings.workspace.plan_pro'),
            default => ucfirst($plan),
        };
    }

    public static function isValid(string $plan): bool
    {
        return in_array($plan, self::keys(), true);
    }

    /**
     * @return 'gray'|'info'|'success'|'warning'|'danger'|null
     */
    public static function badgeColor(string $plan): ?string
    {
        return match ($plan) {
            'free' => 'gray',
            'starter' => 'info',
            'pro' => 'success',
            default => null,
        };
    }
}
