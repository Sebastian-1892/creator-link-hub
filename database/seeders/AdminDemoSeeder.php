<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\WorkspaceProvisioner;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/** Demo-Admin für lokale Entwicklung (admin@example.com / password). */
class AdminDemoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->firstOrNew(['email' => 'admin@example.com']);
        $admin->forceFill([
            'name' => 'Admin',
            'password' => 'password',
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
            'is_admin' => true,
            'onboarding_completed_at' => now(),
        ])->save();

        app(WorkspaceProvisioner::class)->provisionForUser($admin);
    }
}
