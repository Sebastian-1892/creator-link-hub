<?php

namespace App\Services;

use App\Models\SpotifyAccount;
use App\Support\SpotifyUrlParser;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class SpotifyApiService
{
    private const API_BASE = 'https://api.spotify.com/v1';

    public function __construct(
        private readonly SpotifyOAuthService $oauth,
    ) {}

    /**
     * @return array{resource_type: string, provider_id: string, canonical_url: string, title: string, artist: string|null, image: string|null}|null
     */
    public function resolveLatestPlayback(SpotifyAccount $account): ?array
    {
        $token = $this->getValidAccessToken($account);

        $current = $this->getCurrentlyPlaying($token);
        if ($current !== null) {
            return $current;
        }

        return $this->getRecentlyPlayedItem($token);
    }

    /**
     * @return array{resource_type: string, provider_id: string, canonical_url: string, title: string, artist: string|null, image: string|null}|null
     */
    private function getCurrentlyPlaying(string $token): ?array
    {
        $response = Http::withToken($token)
            ->acceptJson()
            ->get(self::API_BASE.'/me/player/currently-playing');

        if ($response->status() === 204 || ! $response->successful()) {
            return null;
        }

        /** @var array<string, mixed>|null $payload */
        $payload = $response->json();

        if (! is_array($payload)) {
            return null;
        }

        return $this->mapPlaybackItem($payload);
    }

    /**
     * @return array{resource_type: string, provider_id: string, canonical_url: string, title: string, artist: string|null, image: string|null}|null
     */
    private function getRecentlyPlayedItem(string $token): ?array
    {
        $response = Http::withToken($token)
            ->acceptJson()
            ->get(self::API_BASE.'/me/player/recently-played', ['limit' => 1]);

        if (! $response->successful()) {
            return null;
        }

        /** @var array{items?: list<array<string, mixed>>}|null $payload */
        $payload = $response->json();
        $item = $payload['items'][0] ?? null;

        if (! is_array($item)) {
            return null;
        }

        return $this->mapPlaybackItem($item);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{resource_type: string, provider_id: string, canonical_url: string, title: string, artist: string|null, image: string|null}|null
     */
    private function mapPlaybackItem(array $payload): ?array
    {
        $track = is_array($payload['track'] ?? null) ? $payload['track'] : null;
        $episode = is_array($payload['episode'] ?? null) ? $payload['episode'] : null;
        $item = is_array($payload['item'] ?? null) ? $payload['item'] : null;

        if ($track === null && $episode === null && $item !== null) {
            $typeHint = is_string($item['type'] ?? null) ? strtolower($item['type']) : null;

            if ($typeHint === 'episode') {
                $episode = $item;
            } else {
                $track = $item;
            }
        }

        $resolvedTrack = $track;
        $resolvedEpisode = $episode;
        $media = $resolvedTrack ?? $resolvedEpisode;

        if (! is_array($media)) {
            return null;
        }

        $type = $resolvedTrack !== null ? 'track' : 'episode';
        $id = is_string($media['id'] ?? null) ? $media['id'] : null;

        if ($id === null || $id === '') {
            return null;
        }

        $parsed = SpotifyUrlParser::parse("spotify:{$type}:{$id}");

        $title = is_string($media['name'] ?? null) ? $media['name'] : 'Spotify';
        $artist = null;
        $image = $this->extractImageUrl($media);

        if ($type === 'track') {
            $artists = is_array($media['artists'] ?? null) ? $media['artists'] : [];
            $names = [];
            foreach ($artists as $artistItem) {
                if (is_array($artistItem) && is_string($artistItem['name'] ?? null)) {
                    $names[] = $artistItem['name'];
                }
            }
            $artist = $names !== [] ? implode(', ', $names) : null;
        } elseif (is_string($media['show']['name'] ?? null)) {
            $artist = $media['show']['name'];
        }

        return [
            'resource_type' => $parsed['resource_type'],
            'provider_id' => $parsed['provider_id'],
            'canonical_url' => $parsed['canonical_url'],
            'title' => $title,
            'artist' => $artist,
            'image' => $image,
        ];
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function extractImageUrl(array $item): ?string
    {
        $images = is_array($item['album']['images'] ?? null)
            ? $item['album']['images']
            : (is_array($item['images'] ?? null) ? $item['images'] : []);

        foreach ($images as $image) {
            if (is_array($image) && is_string($image['url'] ?? null) && $image['url'] !== '') {
                return $image['url'];
            }
        }

        return null;
    }

    public function getValidAccessToken(SpotifyAccount $account): string
    {
        if (! $account->isTokenExpired()) {
            return $account->accessToken();
        }

        try {
            $refreshed = $this->oauth->refreshAccessToken($account->refreshToken());
        } catch (RuntimeException) {
            $account->update(['connection_status' => 'revoked']);

            throw new RuntimeException('Spotify refresh token is invalid.');
        }

        $account->setAccessToken($refreshed['access_token']);
        $account->setRefreshToken($refreshed['refresh_token']);
        $account->expires_at = now()->addSeconds(max(60, $refreshed['expires_in'] - 60));
        $account->connection_status = 'connected';
        $account->save();

        return $refreshed['access_token'];
    }
}
