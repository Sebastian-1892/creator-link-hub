@php
    $clh = $presentation['clh'];
    $settings = $presentation['settings'];
    $avatarUrl = $presentation['avatar_url'];
    $wallpaperUrl = $presentation['wallpaper_url'];
    $cssVarStyle = collect($presentation['css_vars'])
        ->map(fn ($value, $name) => "{$name}: {$value}")
        ->implode('; ');
@endphp

<div
    class="relative mx-auto w-full max-w-[280px] rounded-[2rem] border-8 border-gray-900 shadow-2xl overflow-hidden"
    wire:key="design-preview-{{ $previewKey }}"
>
    <link rel="stylesheet" href="{{ $clh['font_href'] }}" />

    <div
        class="relative min-h-[520px] antialiased overflow-hidden"
        style="{{ $clh['body_style'] }}; {{ $cssVarStyle }}; --clh-accent-soft: color-mix(in srgb, var(--clh-accent) 32%, transparent);"
    >
        @if ($wallpaperUrl && $settings->wallpaperStyle === 'image')
            <div class="pointer-events-none absolute inset-0 bg-cover bg-center" style="background-image: url('{{ $wallpaperUrl }}');"></div>
            <div class="pointer-events-none absolute inset-0" style="background: color-mix(in srgb, var(--clh-bg) 35%, transparent);"></div>
        @elseif ($avatarUrl && $settings->wallpaperStyle !== 'image')
            <div class="pointer-events-none absolute inset-0 opacity-[0.14] blur-2xl scale-110" style="background-image: url('{{ $avatarUrl }}'); background-size: cover; background-position: center;"></div>
            <div class="pointer-events-none absolute inset-0" style="background: color-mix(in srgb, var(--clh-bg) 82%, transparent);"></div>
        @endif

        <div class="relative z-10 px-3 py-6 max-h-[520px] overflow-y-auto">
            <header class="@if ($clh['header_layout'] === 'hero') relative mb-4 @else text-center @endif">
                @if ($clh['header_layout'] === 'banner' && $previewProfile->banner_image_path)
                    <div
                        class="-mx-3 mb-4 h-20 bg-cover bg-center rounded-xl shadow-md"
                        style="background-image: url('{{ \Illuminate\Support\Facades\Storage::url($previewProfile->banner_image_path) }}');"
                        role="img"
                        aria-hidden="true"
                    ></div>
                @endif

                @if ($clh['header_layout'] === 'hero')
                    <div class="relative -mx-3 mb-4 overflow-hidden rounded-xl py-6 px-2" style="background: linear-gradient(180deg, color-mix(in srgb, var(--clh-accent) 25%, transparent), transparent);">
                        <div class="flex flex-col items-center text-center">
                            @include('public.partials.profile-avatar', ['profile' => $previewProfile, 'clh' => $clh])
                            <h2 class="mt-4 text-xl font-bold tracking-tight" style="color: var(--clh-title);">{{ $previewProfile->display_name }}</h2>
                            @if ($previewProfile->bio)
                                <p class="mt-2 max-w-[220px] text-xs leading-relaxed whitespace-pre-line opacity-90" style="color: var(--clh-text-muted);">{{ \Illuminate\Support\Str::limit($previewProfile->bio, 120) }}</p>
                            @endif
                        </div>
                    </div>
                @else
                    @include('public.partials.profile-avatar', ['profile' => $previewProfile, 'clh' => $clh])
                    <h2 class="mt-4 text-xl font-bold tracking-tight" style="color: var(--clh-title);">{{ $previewProfile->display_name }}</h2>
                    @if ($previewProfile->bio)
                        <p class="mt-2 text-xs leading-relaxed whitespace-pre-line opacity-90" style="color: var(--clh-text-muted);">{{ \Illuminate\Support\Str::limit($previewProfile->bio, 100) }}</p>
                    @endif
                @endif
            </header>

            <div class="mt-6 space-y-2">
                <div class="{{ $clh['link_class'] }} text-sm py-3 shadow-md" style="{{ $clh['link_style'] }}">
                    <span class="flex-1 text-center">{{ __('Beispiel-Link') }}</span>
                </div>
                <div class="{{ $clh['link_class'] }} text-sm py-3 shadow-md" style="{{ $clh['link_style'] }}">
                    <span class="flex-1 text-center">{{ __('Zweiter Link') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
