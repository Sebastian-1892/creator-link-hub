@extends('layouts.public', ['profile' => $profile, 'showPlatformBranding' => $showPlatformBranding])

@section('content')
    <div class="max-w-md mx-auto px-4 py-10 pb-24">
        <div class="text-center">
            @if ($profile->avatar_path)
                <img src="{{ \Illuminate\Support\Facades\Storage::url($profile->avatar_path) }}" alt="" class="mx-auto h-24 w-24 rounded-full object-cover ring-2 ring-white/10">
            @else
                <div class="mx-auto h-24 w-24 rounded-full bg-white/10 flex items-center justify-center text-3xl font-bold">
                    {{ \Illuminate\Support\Str::substr($profile->display_name, 0, 1) }}
                </div>
            @endif
            <h1 class="mt-4 text-2xl font-bold">{{ $profile->display_name }}</h1>
            @if ($profile->bio)
                <p class="mt-2 text-sm opacity-90 whitespace-pre-line">{{ $profile->bio }}</p>
            @endif
        </div>

        <div class="mt-10 space-y-3">
            @foreach ($profile->links as $link)
                @php
                    $href = $link->tracking_enabled ? route('links.redirect', $link) : $link->url;
                    $target = $link->opens_in_new_tab ? '_blank' : '_self';
                    $rel = $link->opens_in_new_tab ? 'noopener noreferrer' : null;
                @endphp
                <a href="{{ $href }}" target="{{ $target }}" @if($rel) rel="{{ $rel }}" @endif
                   class="block w-full rounded-2xl px-4 py-4 text-center font-semibold shadow-lg transition hover:opacity-95"
                   style="background: var(--clh-card); color: var(--clh-text); border: 1px solid rgba(255,255,255,0.06);">
                    <span class="text-[var(--clh-accent)]">{{ $link->title }}</span>
                </a>
            @endforeach
        </div>

        @if ($showPlatformBranding)
            <p class="mt-12 text-center text-xs opacity-50">
                {{ __('Erstellt mit') }} <a href="{{ route('home') }}" class="underline">Creator Link Hub</a>
            </p>
        @endif
    </div>
@endsection
