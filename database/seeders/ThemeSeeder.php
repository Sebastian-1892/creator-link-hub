<?php

namespace Database\Seeders;

use App\Models\Theme;
use Illuminate\Database\Seeder;

class ThemeSeeder extends Seeder
{
    /**
     * 30 Profil-Vorlagen (Hintergrund, Text, Akzent, Kartenfarbe).
     */
    public function run(): void
    {
        $themes = [
            ['name' => 'Amber Glow', 'slug' => 'amber-glow', 'variables' => ['bg' => '#1c1917', 'text' => '#fffbeb', 'accent' => '#f59e0b', 'card' => '#292524']],
            ['name' => 'Arctic Ice', 'slug' => 'arctic-ice', 'variables' => ['bg' => '#f0f9ff', 'text' => '#0c4a6e', 'accent' => '#0284c7', 'card' => '#e0f2fe']],
            ['name' => 'Azure Sky', 'slug' => 'azure-sky', 'variables' => ['bg' => '#eff6ff', 'text' => '#1e3a8a', 'accent' => '#3b82f6', 'card' => '#dbeafe']],
            ['name' => 'Berry Pop', 'slug' => 'berry-pop', 'variables' => ['bg' => '#fdf2f8', 'text' => '#831843', 'accent' => '#db2777', 'card' => '#fce7f3']],
            ['name' => 'Charcoal Gold', 'slug' => 'charcoal-gold', 'variables' => ['bg' => '#1c1917', 'text' => '#fef3c7', 'accent' => '#eab308', 'card' => '#292524']],
            ['name' => 'Coral Beach', 'slug' => 'coral-beach', 'variables' => ['bg' => '#fff7ed', 'text' => '#9a3412', 'accent' => '#fb923c', 'card' => '#ffedd5']],
            ['name' => 'Cream Elegant', 'slug' => 'cream-elegant', 'variables' => ['bg' => '#fffbeb', 'text' => '#78350f', 'accent' => '#b45309', 'card' => '#fef3c7']],
            ['name' => 'Crimson Night', 'slug' => 'crimson-night', 'variables' => ['bg' => '#1a0505', 'text' => '#fecaca', 'accent' => '#ef4444', 'card' => '#2d0a0a']],
            ['name' => 'Emerald City', 'slug' => 'emerald-city', 'variables' => ['bg' => '#022c22', 'text' => '#d1fae5', 'accent' => '#34d399', 'card' => '#064e3b']],
            ['name' => 'Espresso Warm', 'slug' => 'espresso-warm', 'variables' => ['bg' => '#292524', 'text' => '#fefae8', 'accent' => '#b45309', 'card' => '#44403c']],
            ['name' => 'Forest', 'slug' => 'forest', 'variables' => ['bg' => '#052e16', 'text' => '#ecfdf5', 'accent' => '#34d399', 'card' => '#064e3b']],
            ['name' => 'Fuchsia Pulse', 'slug' => 'fuchsia-pulse', 'variables' => ['bg' => '#1a0a2e', 'text' => '#fae8ff', 'accent' => '#d946ef', 'card' => '#2e1065']],
            ['name' => 'Graphite Minimal', 'slug' => 'graphite-minimal', 'variables' => ['bg' => '#f4f4f5', 'text' => '#18181b', 'accent' => '#71717a', 'card' => '#e4e4e7']],
            ['name' => 'Lavender Dream', 'slug' => 'lavender-dream', 'variables' => ['bg' => '#f5f3ff', 'text' => '#5b21b6', 'accent' => '#a855f7', 'card' => '#ede9fe']],
            ['name' => 'Midnight Blue', 'slug' => 'midnight-blue', 'variables' => ['bg' => '#0a1628', 'text' => '#e2e8f0', 'accent' => '#3b82f6', 'card' => '#132447']],
            ['name' => 'Minimal Dark', 'slug' => 'minimal-dark', 'variables' => ['bg' => '#0f172a', 'text' => '#f8fafc', 'accent' => '#22d3ee', 'card' => '#1e293b']],
            ['name' => 'Minimal Light', 'slug' => 'minimal-light', 'variables' => ['bg' => '#f8fafc', 'text' => '#0f172a', 'accent' => '#2563eb', 'card' => '#ffffff']],
            ['name' => 'Mint Fresh', 'slug' => 'mint-fresh', 'variables' => ['bg' => '#ecfdf5', 'text' => '#064e3b', 'accent' => '#10b981', 'card' => '#d1fae5']],
            ['name' => 'Neon', 'slug' => 'neon', 'variables' => ['bg' => '#020617', 'text' => '#e0e7ff', 'accent' => '#a78bfa', 'card' => '#0f172a']],
            ['name' => 'Nord Frost', 'slug' => 'nord-frost', 'variables' => ['bg' => '#eceff4', 'text' => '#2e3440', 'accent' => '#5e81ac', 'card' => '#e5e9f0']],
            ['name' => 'Obsidian Red', 'slug' => 'obsidian-red', 'variables' => ['bg' => '#0a0a0a', 'text' => '#fecaca', 'accent' => '#dc2626', 'card' => '#171717']],
            ['name' => 'Ocean Deep', 'slug' => 'ocean-deep', 'variables' => ['bg' => '#031525', 'text' => '#e0f2fe', 'accent' => '#0ea5e9', 'card' => '#082f49']],
            ['name' => 'Paper', 'slug' => 'paper', 'variables' => ['bg' => '#fafaf9', 'text' => '#1c1917', 'accent' => '#d97706', 'card' => '#ffffff']],
            ['name' => 'Peach Soft', 'slug' => 'peach-soft', 'variables' => ['bg' => '#fff5f5', 'text' => '#7f1d1d', 'accent' => '#fda4af', 'card' => '#ffe4e6']],
            ['name' => 'Rose Garden', 'slug' => 'rose-garden', 'variables' => ['bg' => '#fff1f2', 'text' => '#881337', 'accent' => '#f43f5e', 'card' => '#ffe4e6']],
            ['name' => 'Sage Calm', 'slug' => 'sage-calm', 'variables' => ['bg' => '#f6f7f4', 'text' => '#3f6212', 'accent' => '#84cc16', 'card' => '#ecfccb']],
            ['name' => 'Sand Dune', 'slug' => 'sand-dune', 'variables' => ['bg' => '#faf5ef', 'text' => '#44403c', 'accent' => '#d97706', 'card' => '#f5e6d3']],
            ['name' => 'Slate Corporate', 'slug' => 'slate-corporate', 'variables' => ['bg' => '#f8fafc', 'text' => '#0f172a', 'accent' => '#64748b', 'card' => '#e2e8f0']],
            ['name' => 'Sunset', 'slug' => 'sunset', 'variables' => ['bg' => '#1a0a1f', 'text' => '#fff1f2', 'accent' => '#fb7185', 'card' => '#2a1025']],
            ['name' => 'Violet Haze', 'slug' => 'violet-haze', 'variables' => ['bg' => '#2e1065', 'text' => '#ede9fe', 'accent' => '#8b5cf6', 'card' => '#4c1d95']],
        ];

        foreach ($themes as $t) {
            Theme::query()->updateOrCreate(
                ['slug' => $t['slug']],
                $t
            );
        }
    }
}
