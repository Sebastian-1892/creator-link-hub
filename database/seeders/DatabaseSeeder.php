<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(ThemeSeeder::class);

        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'is_admin' => true,
            'onboarding_completed_at' => now(),
        ]);

        User::factory()->create([
            'name' => 'Demo Creator',
            'email' => 'creator@example.com',
            'onboarding_completed_at' => now(),
        ]);
    }
}
