<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $brandSvc = app(\App\Services\BrandingService::class);
        $brandCss = $brandSvc->cssVariables();
    @endphp
    <title>@yield('title', $branding['brand_name']) — {{ $branding['brand_name'] }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            @foreach ($brandCss as $prop => $val)
                {{ $prop }}: {{ $val }};
            @endforeach
        }
    </style>
</head>
<body
    class="font-sans antialiased min-h-screen flex flex-col"
    style="color: var(--brand-text); background: linear-gradient(165deg, var(--brand-bg) 0%, var(--brand-bg-alt) 55%, var(--brand-bg) 100%);"
>
    @include('components.cookie-banner')
    <header class="sticky top-0 z-40 border-b backdrop-blur-md" style="border-color: var(--brand-border); background: color-mix(in srgb, var(--brand-bg) 88%, transparent);">
        <div class="max-w-6xl mx-auto px-4 py-4 flex flex-wrap items-center justify-between gap-4">
            <a href="{{ route('home') }}" class="flex items-center gap-3 font-bold tracking-tight text-lg" style="color: var(--brand-text);">
                @if (! empty($branding['brand_logo_url']))
                    <img src="{{ $branding['brand_logo_url'] }}" alt="" class="h-9 w-auto rounded-lg object-contain">
                @endif
                <span>{{ $branding['brand_name'] }}</span>
            </a>
            <nav class="flex flex-wrap items-center gap-3 text-sm" style="color: var(--brand-text-muted);">
                <a href="{{ route('pricing') }}" class="rounded-full px-3 py-1.5 transition hover:bg-white/10" style="color: inherit;">{{ __('Preise') }}</a>
                <a href="{{ route('faq') }}" class="rounded-full px-3 py-1.5 transition hover:bg-white/10" style="color: inherit;">FAQ</a>
                <a href="{{ route('help') }}" class="rounded-full px-3 py-1.5 transition hover:bg-white/10" style="color: inherit;">{{ __('Hilfe') }}</a>
                @auth
                    <a href="{{ route('dashboard') }}" class="rounded-full px-3 py-1.5 transition hover:bg-white/10" style="color: inherit;">{{ __('Dashboard') }}</a>
                @else
                    <a href="{{ route('login') }}" class="rounded-full px-3 py-1.5 transition hover:bg-white/10" style="color: inherit;">{{ __('Login') }}</a>
                    <a
                        href="{{ route('register') }}"
                        class="rounded-full px-5 py-2 font-semibold shadow-lg transition hover:opacity-95 hover:-translate-y-0.5"
                        style="background: var(--brand-primary); color: var(--brand-primary-contrast);"
                    >{{ __('Starten') }}</a>
                @endauth
            </nav>
        </div>
    </header>
    <main class="flex-1">
        @yield('content')
    </main>
    <footer class="mt-20 border-t py-14 text-sm" style="border-color: var(--brand-border); color: var(--brand-text-muted);">
        <div class="max-w-6xl mx-auto px-4 grid gap-10 sm:grid-cols-3">
            <div>
                <p class="font-semibold" style="color: var(--brand-text);">{{ $branding['brand_name'] }}</p>
                <p class="mt-3 leading-relaxed">{{ $branding['marketing']['footer_tagline'] }}</p>
            </div>
            <div>
                <p class="font-semibold" style="color: var(--brand-text);">{{ __('Navigation') }}</p>
                <ul class="mt-3 space-y-2">
                    <li><a href="{{ route('pricing') }}" class="underline-offset-4 hover:underline">{{ __('Preise') }}</a></li>
                    <li><a href="{{ route('faq') }}" class="underline-offset-4 hover:underline">FAQ</a></li>
                    <li><a href="{{ route('help') }}" class="underline-offset-4 hover:underline">{{ __('Hilfe') }}</a></li>
                </ul>
            </div>
            <div>
                <p class="font-semibold" style="color: var(--brand-text);">{{ __('Rechtliches') }}</p>
                <ul class="mt-3 space-y-2">
                    <li><a href="{{ route('legal.impressum') }}" class="underline-offset-4 hover:underline">{{ __('Impressum') }}</a></li>
                    <li><a href="{{ route('legal.datenschutz') }}" class="underline-offset-4 hover:underline">{{ __('Datenschutz') }}</a></li>
                    <li><a href="{{ route('legal.agb') }}" class="underline-offset-4 hover:underline">AGB</a></li>
                </ul>
            </div>
        </div>
        <div class="max-w-6xl mx-auto px-4 mt-10 pt-6 border-t text-xs opacity-80" style="border-color: var(--brand-border);">
            © {{ date('Y') }} {{ $branding['brand_name'] }}
        </div>
    </footer>
</body>
</html>
