<?php

namespace Database\Seeders;

use App\Models\Theme;
use Illuminate\Database\Seeder;

class ThemeSeeder extends Seeder
{
    /**
     * Farb-Themes + Layout-Templates (button_style, background_style, font_family, card_style, template_group).
     */
    public function run(): void
    {
        $groupBySlug = [
            'arctic-ice' => 'light',
            'azure-sky' => 'light',
            'cream-elegant' => 'light',
            'mint-fresh' => 'light',
            'sand-dune' => 'light',
            'berry-pop' => 'colorful',
            'coral-beach' => 'colorful',
            'lavender-dream' => 'colorful',
            'peach-soft' => 'colorful',
            'rose-garden' => 'colorful',
            'graphite-minimal' => 'minimal',
            'minimal-light' => 'minimal',
            'nord-frost' => 'minimal',
            'paper' => 'minimal',
            'sage-calm' => 'minimal',
            'slate-corporate' => 'minimal',
            'amber-glow' => 'dark',
            'charcoal-gold' => 'dark',
            'crimson-night' => 'dark',
            'emerald-city' => 'dark',
            'espresso-warm' => 'dark',
            'forest' => 'dark',
            'fuchsia-pulse' => 'colorful',
            'midnight-blue' => 'dark',
            'minimal-dark' => 'minimal',
            'neon' => 'colorful',
            'obsidian-red' => 'dark',
            'ocean-deep' => 'dark',
            'sunset' => 'colorful',
            'violet-haze' => 'colorful',
        ];

        $themes = [
            ['name' => 'Amber Glow', 'slug' => 'amber-glow', 'variables' => ['bg' => '#1c1917', 'text' => '#fffbeb', 'accent' => '#f59e0b', 'card' => '#292524'], 'button_style' => 'pill', 'background_style' => 'solid', 'font_family' => 'figtree', 'card_style' => 'flat'],
            ['name' => 'Arctic Ice', 'slug' => 'arctic-ice', 'variables' => ['bg' => '#f0f9ff', 'text' => '#0c4a6e', 'accent' => '#0284c7', 'card' => '#e0f2fe'], 'button_style' => 'rounded', 'background_style' => 'solid', 'font_family' => 'inter', 'card_style' => 'elevated'],
            ['name' => 'Azure Sky', 'slug' => 'azure-sky', 'variables' => ['bg' => '#eff6ff', 'text' => '#1e3a8a', 'accent' => '#3b82f6', 'card' => '#dbeafe'], 'button_style' => 'pill', 'background_style' => 'solid', 'font_family' => 'dm-sans', 'card_style' => 'flat'],
            ['name' => 'Berry Pop', 'slug' => 'berry-pop', 'variables' => ['bg' => '#fdf2f8', 'text' => '#831843', 'accent' => '#db2777', 'card' => '#fce7f3'], 'button_style' => 'rounded', 'background_style' => 'solid', 'font_family' => 'dm-sans', 'card_style' => 'elevated'],
            ['name' => 'Charcoal Gold', 'slug' => 'charcoal-gold', 'variables' => ['bg' => '#1c1917', 'text' => '#fef3c7', 'accent' => '#eab308', 'card' => '#292524'], 'button_style' => 'shadow', 'background_style' => 'solid', 'font_family' => 'playfair', 'card_style' => 'bordered'],
            ['name' => 'Coral Beach', 'slug' => 'coral-beach', 'variables' => ['bg' => '#fff7ed', 'text' => '#9a3412', 'accent' => '#fb923c', 'card' => '#ffedd5'], 'button_style' => 'pill', 'background_style' => 'solid', 'font_family' => 'figtree', 'card_style' => 'flat'],
            ['name' => 'Cream Elegant', 'slug' => 'cream-elegant', 'variables' => ['bg' => '#fffbeb', 'text' => '#78350f', 'accent' => '#b45309', 'card' => '#fef3c7'], 'button_style' => 'outline', 'background_style' => 'solid', 'font_family' => 'playfair', 'card_style' => 'flat'],
            ['name' => 'Crimson Night', 'slug' => 'crimson-night', 'variables' => ['bg' => '#1a0505', 'text' => '#fecaca', 'accent' => '#ef4444', 'card' => '#2d0a0a'], 'button_style' => 'square', 'background_style' => 'gradient', 'font_family' => 'inter', 'card_style' => 'bordered'],
            ['name' => 'Emerald City', 'slug' => 'emerald-city', 'variables' => ['bg' => '#022c22', 'text' => '#d1fae5', 'accent' => '#34d399', 'card' => '#064e3b'], 'button_style' => 'pill', 'background_style' => 'solid', 'font_family' => 'dm-sans', 'card_style' => 'flat'],
            ['name' => 'Espresso Warm', 'slug' => 'espresso-warm', 'variables' => ['bg' => '#292524', 'text' => '#fefae8', 'accent' => '#b45309', 'card' => '#44403c'], 'button_style' => 'rounded', 'background_style' => 'noise', 'font_family' => 'figtree', 'card_style' => 'elevated'],
            ['name' => 'Forest', 'slug' => 'forest', 'variables' => ['bg' => '#052e16', 'text' => '#ecfdf5', 'accent' => '#34d399', 'card' => '#064e3b'], 'button_style' => 'outline', 'background_style' => 'solid', 'font_family' => 'inter', 'card_style' => 'bordered'],
            ['name' => 'Fuchsia Pulse', 'slug' => 'fuchsia-pulse', 'variables' => ['bg' => '#1a0a2e', 'text' => '#fae8ff', 'accent' => '#d946ef', 'card' => '#2e1065'], 'button_style' => 'glass', 'background_style' => 'radial-glow', 'font_family' => 'dm-sans', 'card_style' => 'glass'],
            ['name' => 'Graphite Minimal', 'slug' => 'graphite-minimal', 'variables' => ['bg' => '#f4f4f5', 'text' => '#18181b', 'accent' => '#71717a', 'card' => '#e4e4e7'], 'button_style' => 'square', 'background_style' => 'solid', 'font_family' => 'inter', 'card_style' => 'flat'],
            ['name' => 'Lavender Dream', 'slug' => 'lavender-dream', 'variables' => ['bg' => '#f5f3ff', 'text' => '#5b21b6', 'accent' => '#a855f7', 'card' => '#ede9fe'], 'button_style' => 'pill', 'background_style' => 'pattern-dots', 'font_family' => 'figtree', 'card_style' => 'elevated'],
            ['name' => 'Midnight Blue', 'slug' => 'midnight-blue', 'variables' => ['bg' => '#0a1628', 'text' => '#e2e8f0', 'accent' => '#3b82f6', 'card' => '#132447'], 'button_style' => 'rounded', 'background_style' => 'gradient', 'font_family' => 'inter', 'card_style' => 'bordered'],
            ['name' => 'Minimal Dark', 'slug' => 'minimal-dark', 'variables' => ['bg' => '#0f172a', 'text' => '#f8fafc', 'accent' => '#22d3ee', 'card' => '#1e293b'], 'button_style' => 'outline', 'background_style' => 'solid', 'font_family' => 'inter', 'card_style' => 'flat'],
            ['name' => 'Minimal Light', 'slug' => 'minimal-light', 'variables' => ['bg' => '#f8fafc', 'text' => '#0f172a', 'accent' => '#2563eb', 'card' => '#ffffff'], 'button_style' => 'square', 'background_style' => 'solid', 'font_family' => 'inter', 'card_style' => 'flat'],
            ['name' => 'Mint Fresh', 'slug' => 'mint-fresh', 'variables' => ['bg' => '#ecfdf5', 'text' => '#064e3b', 'accent' => '#10b981', 'card' => '#d1fae5'], 'button_style' => 'pill', 'background_style' => 'solid', 'font_family' => 'dm-sans', 'card_style' => 'elevated'],
            ['name' => 'Neon', 'slug' => 'neon', 'variables' => ['bg' => '#020617', 'text' => '#e0e7ff', 'accent' => '#a78bfa', 'card' => '#0f172a'], 'button_style' => 'glass', 'background_style' => 'noise', 'font_family' => 'space-mono', 'card_style' => 'glass'],
            ['name' => 'Nord Frost', 'slug' => 'nord-frost', 'variables' => ['bg' => '#eceff4', 'text' => '#2e3440', 'accent' => '#5e81ac', 'card' => '#e5e9f0'], 'button_style' => 'rounded', 'background_style' => 'solid', 'font_family' => 'inter', 'card_style' => 'bordered'],
            ['name' => 'Obsidian Red', 'slug' => 'obsidian-red', 'variables' => ['bg' => '#0a0a0a', 'text' => '#fecaca', 'accent' => '#dc2626', 'card' => '#171717'], 'button_style' => 'shadow', 'background_style' => 'solid', 'font_family' => 'inter', 'card_style' => 'flat'],
            ['name' => 'Ocean Deep', 'slug' => 'ocean-deep', 'variables' => ['bg' => '#031525', 'text' => '#e0f2fe', 'accent' => '#0ea5e9', 'card' => '#082f49'], 'button_style' => 'pill', 'background_style' => 'radial-glow', 'font_family' => 'dm-sans', 'card_style' => 'elevated'],
            ['name' => 'Paper', 'slug' => 'paper', 'variables' => ['bg' => '#fafaf9', 'text' => '#1c1917', 'accent' => '#d97706', 'card' => '#ffffff'], 'button_style' => 'square', 'background_style' => 'solid', 'font_family' => 'figtree', 'card_style' => 'bordered'],
            ['name' => 'Peach Soft', 'slug' => 'peach-soft', 'variables' => ['bg' => '#fff5f5', 'text' => '#7f1d1d', 'accent' => '#fda4af', 'card' => '#ffe4e6'], 'button_style' => 'pill', 'background_style' => 'solid', 'font_family' => 'dm-sans', 'card_style' => 'pill'],
            ['name' => 'Rose Garden', 'slug' => 'rose-garden', 'variables' => ['bg' => '#fff1f2', 'text' => '#881337', 'accent' => '#f43f5e', 'card' => '#ffe4e6'], 'button_style' => 'rounded', 'background_style' => 'pattern-dots', 'font_family' => 'figtree', 'card_style' => 'elevated'],
            ['name' => 'Sage Calm', 'slug' => 'sage-calm', 'variables' => ['bg' => '#f6f7f4', 'text' => '#3f6212', 'accent' => '#84cc16', 'card' => '#ecfccb'], 'button_style' => 'outline', 'background_style' => 'solid', 'font_family' => 'dm-sans', 'card_style' => 'flat'],
            ['name' => 'Sand Dune', 'slug' => 'sand-dune', 'variables' => ['bg' => '#faf5ef', 'text' => '#44403c', 'accent' => '#d97706', 'card' => '#f5e6d3'], 'button_style' => 'pill', 'background_style' => 'solid', 'font_family' => 'playfair', 'card_style' => 'flat'],
            ['name' => 'Slate Corporate', 'slug' => 'slate-corporate', 'variables' => ['bg' => '#f8fafc', 'text' => '#0f172a', 'accent' => '#64748b', 'card' => '#e2e8f0'], 'button_style' => 'square', 'background_style' => 'solid', 'font_family' => 'inter', 'card_style' => 'bordered'],
            ['name' => 'Sunset', 'slug' => 'sunset', 'variables' => ['bg' => '#1a0a1f', 'text' => '#fff1f2', 'accent' => '#fb7185', 'card' => '#2a1025'], 'button_style' => 'rounded', 'background_style' => 'gradient', 'font_family' => 'dm-sans', 'card_style' => 'elevated'],
            ['name' => 'Violet Haze', 'slug' => 'violet-haze', 'variables' => ['bg' => '#2e1065', 'text' => '#ede9fe', 'accent' => '#8b5cf6', 'card' => '#4c1d95'], 'button_style' => 'glass', 'background_style' => 'radial-glow', 'font_family' => 'inter', 'card_style' => 'glass'],
        ];

        foreach ($themes as $t) {
            $slug = $t['slug'];
            Theme::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    ...$t,
                    'template_group' => $groupBySlug[$slug] ?? 'light',
                ]
            );
        }

        $layoutTemplates = [
            [
                'name' => 'Heylink Classic',
                'slug' => 'heylink-classic',
                'variables' => ['bg' => '#fdfaf6', 'text' => '#1a1a1a', 'accent' => '#dc4b3f', 'card' => '#ffffff'],
                'button_style' => 'pill',
                'background_style' => 'solid',
                'font_family' => 'figtree',
                'card_style' => 'flat',
                'template_group' => 'light',
            ],
            [
                'name' => 'Modern Glass',
                'slug' => 'modern-glass',
                'variables' => ['bg' => '#0c1220', 'text' => '#e2e8f0', 'accent' => '#38bdf8', 'card' => 'rgba(255,255,255,0.08)'],
                'button_style' => 'glass',
                'background_style' => 'radial-glow',
                'font_family' => 'inter',
                'card_style' => 'glass',
                'template_group' => 'dark',
            ],
            [
                'name' => 'Brutalist',
                'slug' => 'brutalist',
                'variables' => ['bg' => '#f5f0e6', 'text' => '#111111', 'accent' => '#111111', 'card' => '#ffffff'],
                'button_style' => 'square',
                'background_style' => 'pattern-grid',
                'font_family' => 'space-mono',
                'card_style' => 'bordered',
                'template_group' => 'minimal',
            ],
            [
                'name' => 'Elegant Editorial',
                'slug' => 'elegant-editorial',
                'variables' => ['bg' => '#faf7f2', 'text' => '#2d2a26', 'accent' => '#8b7355', 'card' => '#ffffff'],
                'button_style' => 'outline',
                'background_style' => 'solid',
                'font_family' => 'playfair',
                'card_style' => 'flat',
                'template_group' => 'light',
            ],
            [
                'name' => 'Sunset Gradient',
                'slug' => 'sunset-gradient',
                'variables' => ['bg' => '#2d0a14', 'text' => '#fff5f5', 'accent' => '#fb923c', 'card' => 'rgba(255,255,255,0.06)'],
                'button_style' => 'rounded',
                'background_style' => 'gradient',
                'font_family' => 'dm-sans',
                'card_style' => 'elevated',
                'template_group' => 'colorful',
            ],
            [
                'name' => 'Minimal Mono',
                'slug' => 'minimal-mono',
                'variables' => ['bg' => '#ffffff', 'text' => '#171717', 'accent' => '#171717', 'card' => '#f5f5f5'],
                'button_style' => 'outline',
                'background_style' => 'solid',
                'font_family' => 'inter',
                'card_style' => 'flat',
                'template_group' => 'minimal',
            ],
            [
                'name' => 'Glow Pop',
                'slug' => 'glow-pop',
                'variables' => ['bg' => '#0b0f1a', 'text' => '#f8fafc', 'accent' => '#c084fc', 'card' => 'rgba(147,51,234,0.15)'],
                'button_style' => 'glass',
                'background_style' => 'noise',
                'font_family' => 'dm-sans',
                'card_style' => 'pill',
                'template_group' => 'dark',
            ],
            [
                'name' => 'Soft Pastel',
                'slug' => 'soft-pastel',
                'variables' => ['bg' => '#eef6ff', 'text' => '#334155', 'accent' => '#6366f1', 'card' => '#ffffff'],
                'button_style' => 'pill',
                'background_style' => 'solid',
                'font_family' => 'dm-sans',
                'card_style' => 'elevated',
                'template_group' => 'colorful',
            ],
            [
                'name' => 'Tech Night',
                'slug' => 'tech-night',
                'variables' => ['bg' => '#050508', 'text' => '#e4e4e7', 'accent' => '#22c55e', 'card' => '#18181b'],
                'button_style' => 'shadow',
                'background_style' => 'pattern-grid',
                'font_family' => 'space-mono',
                'card_style' => 'bordered',
                'template_group' => 'dark',
            ],
            [
                'name' => 'Magazine',
                'slug' => 'magazine',
                'variables' => ['bg' => '#ffffff', 'text' => '#111827', 'accent' => '#dc2626', 'card' => '#f9fafb'],
                'button_style' => 'square',
                'background_style' => 'solid',
                'font_family' => 'playfair',
                'card_style' => 'bordered',
                'template_group' => 'light',
            ],
            [
                'name' => 'Confetti',
                'slug' => 'confetti',
                'variables' => ['bg' => '#ffffff', 'text' => '#1e293b', 'accent' => '#ec4899', 'card' => '#fef3c7'],
                'button_style' => 'rounded',
                'background_style' => 'pattern-dots',
                'font_family' => 'figtree',
                'card_style' => 'flat',
                'template_group' => 'colorful',
            ],
            [
                'name' => 'Pure Light',
                'slug' => 'pure-light',
                'variables' => ['bg' => '#ffffff', 'text' => '#1a1a1a', 'accent' => '#dc4b3f', 'card' => '#fdfaf6'],
                'button_style' => 'pill',
                'background_style' => 'solid',
                'font_family' => 'figtree',
                'card_style' => 'flat',
                'template_group' => 'light',
            ],
        ];

        foreach ($layoutTemplates as $t) {
            Theme::query()->updateOrCreate(
                ['slug' => $t['slug']],
                $t
            );
        }
    }
}
