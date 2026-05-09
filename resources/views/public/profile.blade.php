@extends('layouts.public', ['profile' => $profile, 'showPlatformBranding' => $showPlatformBranding])

@section('content')
    @php
        $clh = clh_public_theme($profile);
    @endphp
    <div class="max-w-md mx-auto px-4 py-12 pb-28">
        <header class="text-center">
            @if ($profile->avatar_path)
                <img
                    src="{{ \Illuminate\Support\Facades\Storage::url($profile->avatar_path) }}"
                    alt=""
                    class="{{ $clh['avatar_class'] }}"
                    style="{{ $clh['avatar_style'] }}"
                >
            @else
                <div
                    class="{{ $clh['placeholder_avatar_class'] }} text-4xl"
                    style="{{ $clh['placeholder_avatar_style'] }}"
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
                    class="{{ $clh['link_class'] }} shadow-md hover:shadow-xl"
                    style="{{ $clh['link_style'] }}"
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
