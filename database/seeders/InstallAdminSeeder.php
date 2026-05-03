<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\WorkspaceProvisioner;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Wird von scripts/install-server.sh per Umgebungsvariablen aufgerufen:
 * CLH_ADMIN_EMAIL, CLH_ADMIN_PASSWORD, optional CLH_ADMIN_NAME.
 */
class InstallAdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = trim((string) getenv('CLH_ADMIN_EMAIL'));
        $password = (string) getenv('CLH_ADMIN_PASSWORD');
        $name = trim((string) (getenv('CLH_ADMIN_NAME') ?: 'Administrator'));

        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('CLH_ADMIN_EMAIL ist leer oder ungültig.');
        }
        if ($password === '' || strlen($password) < 8) {
            throw new RuntimeException('CLH_ADMIN_PASSWORD muss mindestens 8 Zeichen haben.');
        }
        if ($name === '') {
            $name = 'Administrator';
        }

        $user = User::query()->firstOrNew(['email' => $email]);
        $user->forceFill([
            'name' => $name,
            'password' => $password,
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
            'is_admin' => true,
            'onboarding_completed_at' => now(),
        ])->save();

        app(WorkspaceProvisioner::class)->provisionForUser($user);
    }
}
