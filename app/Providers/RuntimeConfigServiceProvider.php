<?php

namespace App\Providers;

use App\Services\SettingsService;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class RuntimeConfigServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SettingsService::class);
    }

    public function boot(): void
    {
        try {
            if (! Schema::hasTable('settings')) {
                return;
            }

            $this->app->make(SettingsService::class)->applyRuntimeConfigOverrides();
        } catch (\Throwable) {
            // Migration noch nicht gelaufen oder DB nicht erreichbar (z. B. package:discover).
        }
    }
}
