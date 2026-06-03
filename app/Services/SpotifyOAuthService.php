<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class SpotifyOAuthService
{
    private const AUTHORIZE_URL = 'https://accounts.spotify.com/authorize';

    private const TOKEN_URL = 'https://accounts.spotify.com/api/token';

    /** @var list<string> */
    public const SCOPES = [
        'user-read-currently-playing',
        'user-read-recently-played',
    ];

    public function redirectUri(): string
    {
        $configured = config('services.spotify.redirect');

        if (is_string($configured) && $configured !== '') {
            return $configured;
        }

        return rtrim((string) config('app.url'), '/').'/hub/spotify/callback';
    }

    public function authorizationUrl(string $state): string
    {
        $clientId = $this->clientId();

        $query = http_build_query([
            'client_id' => $clientId,
            'response_type' => 'code',
            'redirect_uri' => $this->redirectUri(),
            'scope' => implode(' ', self::SCOPES),
            'state' => $state,
            'show_dialog' => 'false',
        ]);

        return self::AUTHORIZE_URL.'?'.$query;
    }

    /**
     * @return array{access_token: string, refresh_token: string, expires_in: int, scope?: string}
     */
    public function exchangeCode(string $code): array
    {
        $response = Http::asForm()
            ->withBasicAuth($this->clientId(), $this->clientSecret())
            ->post(self::TOKEN_URL, [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => $this->redirectUri(),
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('Spotify token exchange failed.');
        }

        /** @var array{access_token?: string, refresh_token?: string, expires_in?: int} $data */
        $data = $response->json();

        if (! is_string($data['access_token'] ?? null) || ! is_string($data['refresh_token'] ?? null)) {
            throw new RuntimeException('Spotify token response incomplete.');
        }

        return [
            'access_token' => $data['access_token'],
            'refresh_token' => $data['refresh_token'],
            'expires_in' => (int) ($data['expires_in'] ?? 3600),
            'scope' => is_string($data['scope'] ?? null) ? $data['scope'] : null,
        ];
    }

    /**
     * @return array{access_token: string, refresh_token: string, expires_in: int}
     */
    public function refreshAccessToken(string $refreshToken): array
    {
        $response = Http::asForm()
            ->withBasicAuth($this->clientId(), $this->clientSecret())
            ->post(self::TOKEN_URL, [
                'grant_type' => 'refresh_token',
                'refresh_token' => $refreshToken,
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('Spotify token refresh failed.');
        }

        /** @var array{access_token?: string, refresh_token?: string, expires_in?: int} $data */
        $data = $response->json();

        if (! is_string($data['access_token'] ?? null)) {
            throw new RuntimeException('Spotify refresh response incomplete.');
        }

        return [
            'access_token' => $data['access_token'],
            'refresh_token' => is_string($data['refresh_token'] ?? null) ? $data['refresh_token'] : $refreshToken,
            'expires_in' => (int) ($data['expires_in'] ?? 3600),
        ];
    }

    public function generateState(): string
    {
        return Str::random(40);
    }

    private function clientId(): string
    {
        $clientId = config('services.spotify.client_id');

        if (! is_string($clientId) || $clientId === '') {
            throw new RuntimeException('Spotify client_id is not configured.');
        }

        return $clientId;
    }

    private function clientSecret(): string
    {
        $secret = config('services.spotify.client_secret');

        if (! is_string($secret) || $secret === '') {
            throw new RuntimeException('Spotify client_secret is not configured.');
        }

        return $secret;
    }
}
