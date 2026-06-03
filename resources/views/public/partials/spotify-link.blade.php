@if ($link->isSpotifyEmbed())
    <x-spotify-embed
        :embed-url="$link->spotifyEmbedUrl()"
        :resource-type="$link->provider_resource_type ?? 'track'"
        :title="$link->spotifyDisplayTitle()"
    />
@endif
