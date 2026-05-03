<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $profile->display_name }} — Creator Link Hub</title>
    <meta name="description" content="{{ \Illuminate\Support\Str::limit(strip_tags($profile->bio ?? ''), 160) }}">
    <meta property="og:title" content="{{ $profile->display_name }}">
    <meta property="og:description" content="{{ \Illuminate\Support\Str::limit(strip_tags($profile->bio ?? ''), 200) }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @php
        $vars = array_merge(
            $profile->theme?->variables ?? [],
            $profile->theme_variables ?? []
        );
    @endphp
    <style>
        :root {
            --clh-bg: {{ $vars['bg'] ?? '#0f172a' }};
            --clh-text: {{ $vars['text'] ?? '#f8fafc' }};
            --clh-accent: {{ $vars['accent'] ?? '#22d3ee' }};
            --clh-card: {{ $vars['card'] ?? '#1e293b' }};
        }
    </style>
</head>
<body class="min-h-screen antialiased" style="background: var(--clh-bg); color: var(--clh-text);">
    @yield('content')
</body>
</html>
