<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $profile->display_name }} — {{ $branding['brand_name'] }}</title>
    <meta name="description" content="{{ \Illuminate\Support\Str::limit(strip_tags($profile->bio ?? ''), 160) }}">
    <meta property="og:title" content="{{ $profile->display_name }}">
    <meta property="og:description" content="{{ \Illuminate\Support\Str::limit(strip_tags($profile->bio ?? ''), 200) }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @php
        $fallback = app(\App\Services\BrandingService::class)->profileThemeFallbackVariables();
        $vars = array_merge(
            $fallback,
            $profile->theme?->variables ?? [],
            $profile->theme_variables ?? []
        );
        $bg = $vars['bg'] ?? $fallback['bg'];
        $text = $vars['text'] ?? $fallback['text'];
        $accent = $vars['accent'] ?? $fallback['accent'];
        $card = $vars['card'] ?? $fallback['card'];
        $border = $vars['border'] ?? $fallback['border'];
        $avatarUrl = $profile->avatar_path ? \Illuminate\Support\Facades\Storage::url($profile->avatar_path) : null;
    @endphp
    <style>
        :root {
            --clh-bg: {{ $bg }};
            --clh-text: {{ $text }};
            --clh-accent: {{ $accent }};
            --clh-card: {{ $card }};
            --clh-border: {{ $border }};
            --clh-accent-soft: color-mix(in srgb, {{ $accent }} 32%, transparent);
            --clh-text-muted: {{ $vars['text_muted'] ?? $fallback['text_muted'] }};
        }
    </style>
</head>
<body
    class="min-h-screen antialiased relative overflow-x-hidden"
    style="color: var(--clh-text); background: radial-gradient(ellipse 90% 55% at 50% -15%, var(--clh-accent-soft), transparent 55%), linear-gradient(180deg, var(--clh-bg), color-mix(in srgb, var(--clh-bg) 88%, #000));"
>
    @if ($avatarUrl)
        <div class="pointer-events-none fixed inset-0 -z-10 opacity-[0.14] blur-3xl scale-110" style="background-image: url('{{ $avatarUrl }}'); background-size: cover; background-position: center;"></div>
        <div class="pointer-events-none fixed inset-0 -z-10" style="background: color-mix(in srgb, var(--clh-bg) 82%, transparent);"></div>
    @endif
    @yield('content')
</body>
</html>
