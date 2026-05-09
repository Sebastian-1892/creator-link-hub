@props([
    'p' => [],
])

@php
    $d = is_array($p) ? $p : [];
    $primary = $d['color_primary'] ?? '#6366f1';
    $accent = $d['color_accent'] ?? '#c084fc';
    $bg = $d['color_bg'] ?? '#0b0914';
    $bgAlt = $d['color_bg_alt'] ?? '#151023';
    $text = $d['color_text'] ?? '#f8fafc';
    $muted = $d['color_text_muted'] ?? '#94a3b8';
    $card = $d['color_card'] ?? 'rgba(255,255,255,0.07)';
    $border = $d['color_border'] ?? 'rgba(255,255,255,0.14)';
    $eyebrow = $d['marketing_eyebrow'] ?? '';
    $headline = $d['marketing_headline'] ?? '';
    $subline = $d['marketing_subline'] ?? '';
    $cta1 = $d['marketing_cta_primary'] ?? '';
    $cta2 = $d['marketing_cta_secondary'] ?? '';
@endphp

<div
    class="rounded-xl border border-dashed border-gray-300 p-4 dark:border-gray-600"
    style="background: linear-gradient(145deg, {{ $bg }}, {{ $bgAlt }}); color: {{ $text }};"
>
    <p class="text-[10px] font-semibold uppercase tracking-wider" style="color: {{ $accent }};">{{ \Illuminate\Support\Str::limit($eyebrow, 48) }}</p>
    <p class="mt-2 text-lg font-bold leading-snug">{{ \Illuminate\Support\Str::limit($headline, 120) }}</p>
    <p class="mt-2 text-xs leading-relaxed" style="color: {{ $muted }};">{{ \Illuminate\Support\Str::limit($subline, 160) }}</p>
    <div class="mt-4 flex flex-wrap gap-2">
        <span class="rounded-full px-3 py-1 text-[11px] font-semibold" style="background: {{ $primary }}; color: {{ $d['color_primary_contrast'] ?? '#ffffff' }};">
            {{ \Illuminate\Support\Str::limit($cta1, 22) }}
        </span>
        <span class="rounded-full border px-3 py-1 text-[11px] font-semibold" style="border-color: {{ $border }};">
            {{ \Illuminate\Support\Str::limit($cta2, 22) }}
        </span>
    </div>

    <div class="mx-auto mt-6 max-w-[220px] rounded-[24px] border p-4 shadow-lg" style="border-color: {{ $border }}; background: {{ $card }};">
        <div class="mx-auto h-14 w-14 rounded-full border-2" style="border-color: {{ $accent }};"></div>
        <div class="mx-auto mt-3 h-2 w-24 rounded-full opacity-40" style="background: {{ $text }};"></div>
        <div class="mx-auto mt-2 h-2 w-32 rounded-full opacity-25" style="background: {{ $text }};"></div>
        <div class="mt-4 space-y-2">
            <div class="h-9 rounded-xl text-center text-[11px] font-semibold leading-9" style="background: {{ $card }}; border: 1px solid {{ $border }}; color: {{ $primary }};">
                Link 1
            </div>
            <div class="h-9 rounded-xl text-center text-[11px] font-semibold leading-9" style="background: {{ $card }}; border: 1px solid {{ $border }}; color: {{ $primary }};">
                Link 2
            </div>
        </div>
    </div>
</div>
