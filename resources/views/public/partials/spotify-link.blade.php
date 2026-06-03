<div class="clh-spotify-link rounded-2xl overflow-hidden border border-white/40 bg-white/80 shadow-sm">
    @if ($link->isSpotifyEmbed())
        @if ($link->spotifyDisplaySubtitle())
            <div class="px-4 pt-3 pb-1 text-center">
                <p class="text-xs font-medium uppercase tracking-wide opacity-60" style="color: var(--clh-text-muted);">
                    {{ $link->is_dynamic ? __('Gerade auf Spotify') : __('Spotify') }}
                </p>
                <p class="text-sm font-semibold">{{ $link->spotifyDisplayTitle() }}</p>
                <p class="text-xs opacity-75" style="color: var(--clh-text-muted);">{{ $link->spotifyDisplaySubtitle() }}</p>
            </div>
        @endif

        <iframe
            src="{{ $link->spotifyEmbedUrl() }}"
            width="100%"
            height="{{ $link->spotifyEmbedHeight() }}"
            frameborder="0"
            allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture"
            loading="lazy"
            title="{{ $link->spotifyDisplayTitle() }}"
            class="block w-full"
        ></iframe>

        <div class="px-4 py-3 text-center">
            <a
                href="{{ $link->url }}"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-flex items-center justify-center gap-2 rounded-full px-4 py-2 text-sm font-semibold transition hover:opacity-90"
                style="background-color: #1DB954; color: #fff;"
            >
                <x-brand-icon name="spotify" class="h-4 w-4" />
                {{ __('Auf Spotify abspielen') }}
            </a>
        </div>
    @else
        <div class="px-4 py-5 text-center space-y-3">
            <p class="text-sm font-semibold">{{ $link->title }}</p>
            <p class="text-xs opacity-75" style="color: var(--clh-text-muted);">
                {{ $link->is_dynamic ? __('Wird synchronisiert, sobald Spotify-Inhalte verfügbar sind.') : __('Spotify-Inhalt wird geladen…') }}
            </p>
            <a
                href="https://open.spotify.com"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-flex items-center justify-center gap-2 rounded-full px-4 py-2 text-sm font-semibold"
                style="background-color: #1DB954; color: #fff;"
            >
                <x-brand-icon name="spotify" class="h-4 w-4" />
                {{ __('Spotify öffnen') }}
            </a>
        </div>
    @endif
</div>
