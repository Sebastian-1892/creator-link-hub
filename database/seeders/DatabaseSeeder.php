<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\WorkspaceProvisioner;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(ThemeSeeder::class);

        $provisioner = app(WorkspaceProvisioner::class);

        // Avoid User::factory() here: fakerphp/faker is require-dev only (--no-dev installs).
        $admin = User::query()->firstOrNew(['email' => 'admin@example.com']);
        $admin->forceFill([
            'name' => 'Admin',
            'password' => 'password',
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
            'is_admin' => true,
            'onboarding_completed_at' => now(),
        ])->save();
        $provisioner->provisionForUser($admin);

        $creator = User::query()->firstOrNew(['email' => 'creator@example.com']);
        $creator->forceFill([
            'name' => 'Demo Creator',
            'password' => 'password',
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
            'is_admin' => false,
            'onboarding_completed_at' => now(),
        ])->save();
        $provisioner->provisionForUser($creator);
    }
}
