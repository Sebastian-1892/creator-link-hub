<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\WorkspaceProvisioner;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/** Zweiter Demo-Nutzer (creator@example.com / password), kein Admin. */
class DemoCreatorSeeder extends Seeder
{
    public function run(): void
    {
        $creator = User::query()->firstOrNew(['email' => 'creator@example.com']);
        $creator->forceFill([
            'name' => 'Demo Creator',
            'password' => 'password',
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
            'is_admin' => false,
            'onboarding_completed_at' => now(),
        ])->save();

        app(WorkspaceProvisioner::class)->provisionForUser($creator);
    }
}
