@php
    $compact = $compact ?? false;
    $headingTag = $compact ? 'h2' : 'h1';
    $titleClass = $compact
        ? 'mt-4 text-xl font-bold tracking-tight'
        : 'mt-6 text-3xl font-bold tracking-tight';
    $bioClass = $compact
        ? 'mt-2 w-full max-w-[220px] text-xs leading-relaxed whitespace-pre-line text-center break-words [overflow-wrap:anywhere] opacity-90'
        : 'mt-3 w-full text-base sm:text-[0.95rem] leading-relaxed whitespace-pre-line text-center break-words [overflow-wrap:anywhere] opacity-90 px-1';
    $bioLimit = $compact ? 120 : null;
    $bannerHeight = $compact ? 'h-20' : 'h-36 sm:h-44';
    $bannerRadius = $compact ? 'rounded-xl' : 'rounded-2xl';
    $bannerMargin = $compact ? 'mb-4' : 'mb-6';
    $heroPadding = $compact ? 'py-6 px-2' : 'py-10 px-4';
    $heroRadius = $compact ? 'rounded-xl' : 'rounded-2xl';
    $heroMargin = $compact ? 'mb-4' : 'mb-8';
@endphp

<header class="flex w-full flex-col items-center text-center">
    @if ($clh['header_layout'] === 'banner' && $profile->banner_image_path)
        <div
            class="w-full {{ $bannerHeight }} {{ $bannerRadius }} {{ $bannerMargin }} bg-cover bg-center shadow-md"
            style="background-image: url('{{ \Illuminate\Support\Facades\Storage::url($profile->banner_image_path) }}');"
            role="img"
            aria-hidden="true"
        ></div>
    @endif

    @if ($clh['header_layout'] === 'hero')
        <div
            class="w-full {{ $heroMargin }} overflow-hidden {{ $heroRadius }} {{ $heroPadding }}"
            style="background: linear-gradient(180deg, color-mix(in srgb, var(--clh-accent) 25%, transparent), transparent);"
        >
            <div class="flex flex-col items-center text-center">
                @include('public.partials.profile-avatar', ['profile' => $profile, 'clh' => $clh])
                <{{ $headingTag }} class="{{ $titleClass }}" style="color: var(--clh-title);">{{ $profile->display_name }}</{{ $headingTag }}>
                @if ($profile->bio)
                    <p class="{{ $bioClass }} clh-profile-bio" style="color: var(--clh-text-muted);">
                        {{ $bioLimit ? \Illuminate\Support\Str::limit($profile->bio, $bioLimit) : $profile->bio }}
                    </p>
                @endif
            </div>
        </div>
    @else
        @include('public.partials.profile-avatar', ['profile' => $profile, 'clh' => $clh])
        <{{ $headingTag }} class="{{ $titleClass }}" style="color: var(--clh-title);">{{ $profile->display_name }}</{{ $headingTag }}>
        @if ($profile->bio)
            <p class="{{ $bioClass }} clh-profile-bio" style="color: var(--clh-text-muted);">
                {{ $bioLimit ? \Illuminate\Support\Str::limit($profile->bio, $bioLimit) : $profile->bio }}
            </p>
        @endif
    @endif
</header>
