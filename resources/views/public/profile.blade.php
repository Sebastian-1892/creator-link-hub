@extends('layouts.public', ['profile' => $profile, 'showPlatformBranding' => $showPlatformBranding])

@section('content')
    <div class="max-w-md mx-auto px-4 py-12 pb-28">
        <header class="text-center">
            @if ($profile->avatar_path)
                <img
                    src="{{ \Illuminate\Support\Facades\Storage::url($profile->avatar_path) }}"
                    alt=""
                    class="mx-auto h-28 w-28 rounded-full object-cover shadow-xl"
                    style="box-shadow: 0 0 0 4px color-mix(in srgb, var(--clh-accent) 38%, transparent); border: 3px solid color-mix(in srgb, var(--clh-accent) 60%, transparent);"
                >
            @else
                <div
                    class="mx-auto flex h-28 w-28 items-center justify-center rounded-full text-4xl font-bold shadow-xl"
                    style="background: color-mix(in srgb, var(--clh-card) 90%, transparent); color: var(--clh-accent); border: 3px solid color-mix(in srgb, var(--clh-accent) 50%, transparent); box-shadow: 0 0 0 4px color-mix(in srgb, var(--clh-accent) 28%, transparent);"
                >
                    {{ \Illuminate\Support\Str::substr($profile->display_name, 0, 1) }}
                </div>
            @endif
            <h1 class="mt-6 text-3xl font-bold tracking-tight">{{ $profile->display_name }}</h1>
            @if ($profile->bio)
                <p class="mt-3 text-base leading-relaxed whitespace-pre-line opacity-90" style="color: var(--clh-text-muted);">{{ $profile->bio }}</p>
            @endif
        </header>

        <div class="mt-12 space-y-3">
            @foreach ($profile->links as $link)
                @php
                    $href = $link->tracking_enabled ? route('links.redirect', $link) : $link->url;
                    $target = $link->opens_in_new_tab ? '_blank' : '_self';
                    $rel = $link->opens_in_new_tab ? 'noopener noreferrer' : null;
                @endphp
                <a
                    href="{{ $href }}"
                    target="{{ $target }}"
                    @if ($rel) rel="{{ $rel }}" @endif
                    class="group relative flex w-full items-center gap-3 rounded-2xl px-5 py-4 font-semibold shadow-md transition duration-200 hover:-translate-y-0.5 hover:shadow-xl focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2"
                    style="background: var(--clh-card); color: var(--clh-text); border: 1px solid var(--clh-border);"
                >
                    <span class="flex-1 text-center">{{ $link->title }}</span>
                    <span class="absolute right-4 text-lg opacity-0 transition group-hover:opacity-100" style="color: var(--clh-accent);" aria-hidden="true">→</span>
                </a>
            @endforeach
        </div>

        @if ($showPlatformBranding)
            <p class="mt-14 text-center text-xs opacity-55" style="color: var(--clh-text-muted);">
                {{ $branding['bio']['platform_credit'] }}
                <a href="{{ route('home') }}" class="underline decoration-dotted underline-offset-4 hover:opacity-100" style="color: var(--clh-accent);">{{ $branding['bio']['platform_url_label'] }}</a>
            </p>
        @endif
    </div>
@endsection
