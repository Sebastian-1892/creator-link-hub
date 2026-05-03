<?php

namespace Database\Seeders;

use App\Models\Theme;
use Illuminate\Database\Seeder;

class ThemeSeeder extends Seeder
{
    public function run(): void
    {
        $themes = [
            [
                'name' => 'Minimal Dark',
                'slug' => 'minimal-dark',
                'variables' => [
                    'bg' => '#0f172a',
                    'text' => '#f8fafc',
                    'accent' => '#22d3ee',
                    'card' => '#1e293b',
                ],
            ],
            [
                'name' => 'Minimal Light',
                'slug' => 'minimal-light',
                'variables' => [
                    'bg' => '#f8fafc',
                    'text' => '#0f172a',
                    'accent' => '#2563eb',
                    'card' => '#ffffff',
                ],
            ],
            [
                'name' => 'Sunset',
                'slug' => 'sunset',
                'variables' => [
                    'bg' => '#1a0a1f',
                    'text' => '#fff1f2',
                    'accent' => '#fb7185',
                    'card' => '#2a1025',
                ],
            ],
            [
                'name' => 'Forest',
                'slug' => 'forest',
                'variables' => [
                    'bg' => '#052e16',
                    'text' => '#ecfdf5',
                    'accent' => '#34d399',
                    'card' => '#064e3b',
                ],
            ],
            [
                'name' => 'Neon',
                'slug' => 'neon',
                'variables' => [
                    'bg' => '#020617',
                    'text' => '#e0e7ff',
                    'accent' => '#a78bfa',
                    'card' => '#0f172a',
                ],
            ],
            [
                'name' => 'Paper',
                'slug' => 'paper',
                'variables' => [
                    'bg' => '#fafaf9',
                    'text' => '#1c1917',
                    'accent' => '#d97706',
                    'card' => '#ffffff',
                ],
            ],
        ];

        foreach ($themes as $t) {
            Theme::query()->updateOrCreate(
                ['slug' => $t['slug']],
                $t
            );
        }
    }
}
