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
    @php
        $presentation = clh_public_presentation($profile);
        $clhHead = $presentation['clh'];
        $settings = $presentation['settings'];
        $avatarUrl = $presentation['avatar_url'];
        $wallpaperUrl = $presentation['wallpaper_url'];
    @endphp
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="{{ $clhHead['font_href'] }}" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            @foreach ($presentation['css_vars'] as $name => $value)
            {{ $name }}: {{ $value }};
            @endforeach
            --clh-accent-soft: color-mix(in srgb, var(--clh-accent) 32%, transparent);
        }
    </style>
</head>
<body
    class="min-h-screen antialiased relative overflow-x-hidden"
    style="{{ $clhHead['body_style'] }}"
>
    @if ($wallpaperUrl && $settings->wallpaperStyle === 'image')
        <div class="pointer-events-none fixed inset-0 -z-20 bg-cover bg-center" style="background-image: url('{{ $wallpaperUrl }}');"></div>
        <div class="pointer-events-none fixed inset-0 -z-10" style="background: color-mix(in srgb, var(--clh-bg) 35%, transparent);"></div>
    @elseif ($avatarUrl && $settings->wallpaperStyle !== 'image')
        <div class="pointer-events-none fixed inset-0 -z-10 opacity-[0.14] blur-3xl scale-110" style="background-image: url('{{ $avatarUrl }}'); background-size: cover; background-position: center;"></div>
        <div class="pointer-events-none fixed inset-0 -z-10" style="background: color-mix(in srgb, var(--clh-bg) 82%, transparent);"></div>
    @endif
    @yield('content')
</body>
</html>
