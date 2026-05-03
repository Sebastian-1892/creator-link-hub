<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name')) — Creator Link Hub</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-950 text-slate-100">
    @include('components.cookie-banner')
    <header class="border-b border-slate-800">
        <div class="max-w-6xl mx-auto px-4 py-4 flex items-center justify-between gap-4">
            <a href="{{ route('home') }}" class="text-lg font-bold tracking-tight text-white">Creator Link Hub</a>
            <nav class="flex flex-wrap items-center gap-4 text-sm text-slate-300">
                <a href="{{ route('pricing') }}" class="hover:text-white">{{ __('Preise') }}</a>
                <a href="{{ route('faq') }}" class="hover:text-white">FAQ</a>
                <a href="{{ route('help') }}" class="hover:text-white">{{ __('Hilfe') }}</a>
                @auth
                    <a href="{{ route('dashboard') }}" class="hover:text-white">{{ __('Dashboard') }}</a>
                @else
                    <a href="{{ route('login') }}" class="hover:text-white">{{ __('Login') }}</a>
                    <a href="{{ route('register') }}" class="rounded-full bg-cyan-500 px-4 py-2 font-medium text-slate-950 hover:bg-cyan-400">{{ __('Starten') }}</a>
                @endauth
            </nav>
        </div>
    </header>
    <main>
        @yield('content')
    </main>
    <footer class="mt-16 border-t border-slate-800 py-10 text-sm text-slate-500">
        <div class="max-w-6xl mx-auto px-4 flex flex-col sm:flex-row sm:justify-between gap-4">
            <div>© {{ date('Y') }} Creator Link Hub</div>
            <div class="flex gap-4">
                <a href="{{ route('legal.impressum') }}" class="hover:text-slate-300">{{ __('Impressum') }}</a>
                <a href="{{ route('legal.datenschutz') }}" class="hover:text-slate-300">{{ __('Datenschutz') }}</a>
                <a href="{{ route('legal.agb') }}" class="hover:text-slate-300">AGB</a>
            </div>
        </div>
    </footer>
</body>
</html>
