<?php

use App\Jobs\SyncDynamicSpotifyLinksJob;
use App\Models\Link;
use App\Models\Profile;
use App\Models\SpotifyAccount;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

test('spotify oauth callback stores encrypted account', function () {
    config([
        'services.spotify.client_id' => 'test-client-id',
        'services.spotify.client_secret' => 'test-client-secret',
        'services.spotify.redirect' => 'https://example.test/hub/spotify/callback',
    ]);

    $user = User::factory()->create();
    $workspace = $user->currentWorkspace();
    expect($workspace)->not->toBeNull();

    Http::fake([
        'accounts.spotify.com/*' => Http::response([
            'access_token' => 'access-token-123',
            'refresh_token' => 'refresh-token-456',
            'expires_in' => 3600,
            'token_type' => 'Bearer',
        ], 200),
        'api.spotify.com/v1/me' => Http::response([
            'id' => 'spotify-user-1',
            'display_name' => 'Tester',
        ], 200),
    ]);

    $response = $this->actingAs($user)
        ->withSession([
            'spotify_oauth_state' => 'state-123',
            'spotify_oauth_workspace_id' => $workspace->id,
        ])
        ->get(route('spotify.callback', [
            'code' => 'auth-code',
            'state' => 'state-123',
        ]));

    $response->assertRedirect(route('links.manage'));

    $account = SpotifyAccount::query()->where('workspace_id', $workspace->id)->first();

    expect($account)->not->toBeNull()
        ->and($account->spotify_user_id)->toBe('spotify-user-1')
        ->and($account->connection_status)->toBe('connected')
        ->and($account->accessToken())->toBe('access-token-123')
        ->and($account->refreshToken())->toBe('refresh-token-456');
});

test('sync job updates dynamic spotify link from currently playing', function () {
    $user = User::factory()->create();
    $workspace = $user->currentWorkspace();
    $profile = $workspace->profile;
    expect($profile)->not->toBeNull();

    $account = new SpotifyAccount([
        'workspace_id' => $workspace->id,
        'spotify_user_id' => 'spotify-user-1',
        'expires_at' => now()->addHour(),
        'connection_status' => 'connected',
    ]);
    $account->setAccessToken('access-token');
    $account->setRefreshToken('refresh-token');
    $account->save();

    $link = Link::query()->create([
        'profile_id' => $profile->id,
        'link_type' => 'link',
        'title' => 'Gerade auf Spotify',
        'url' => 'https://open.spotify.com',
        'preset_key' => 'spotify',
        'provider' => 'spotify',
        'is_dynamic' => true,
        'position' => 1,
        'is_active' => true,
        'opens_in_new_tab' => false,
        'tracking_enabled' => false,
        'show_icon' => false,
    ]);

    Http::fake([
        'api.spotify.com/v1/me/player/currently-playing' => Http::response([
            'item' => [
                'type' => 'track',
                'id' => '6rqhFgbbKwnb9MLmUQDhG6',
                'name' => 'Song Title',
                'artists' => [['name' => 'Artist One']],
                'album' => ['images' => [['url' => 'https://i.scdn.co/image/cover.jpg']]],
            ],
        ], 200),
    ]);

    (new SyncDynamicSpotifyLinksJob)->handle(app(\App\Services\SpotifyApiService::class));

    $link->refresh();

    expect($link->provider_id)->toBe('6rqhFgbbKwnb9MLmUQDhG6')
        ->and($link->provider_resource_type)->toBe('track')
        ->and($link->cached_title)->toBe('Song Title')
        ->and($link->cached_artist)->toBe('Artist One')
        ->and($link->url)->toBe('https://open.spotify.com/track/6rqhFgbbKwnb9MLmUQDhG6');

    $account->refresh();
    expect($account->last_sync_at)->not->toBeNull();
});

test('sync job marks account revoked when refresh fails', function () {
    config([
        'services.spotify.client_id' => 'test-client-id',
        'services.spotify.client_secret' => 'test-client-secret',
    ]);

    $user = User::factory()->create();
    $workspace = $user->currentWorkspace();
    $profile = $workspace->profile;

    $account = new SpotifyAccount([
        'workspace_id' => $workspace->id,
        'spotify_user_id' => 'spotify-user-1',
        'expires_at' => now()->subMinute(),
        'connection_status' => 'connected',
    ]);
    $account->setAccessToken('expired-token');
    $account->setRefreshToken('bad-refresh-token');
    $account->save();

    Link::query()->create([
        'profile_id' => $profile->id,
        'link_type' => 'link',
        'title' => 'Gerade auf Spotify',
        'url' => 'https://open.spotify.com',
        'preset_key' => 'spotify',
        'provider' => 'spotify',
        'is_dynamic' => true,
        'position' => 1,
        'is_active' => true,
        'opens_in_new_tab' => false,
        'tracking_enabled' => false,
        'show_icon' => false,
    ]);

    Http::fake([
        'accounts.spotify.com/*' => Http::response(['error' => 'invalid_grant'], 400),
    ]);

    (new SyncDynamicSpotifyLinksJob)->handle(app(\App\Services\SpotifyApiService::class));

    expect($account->fresh()->connection_status)->toBe('revoked');
});

test('dynamic spotify link renders updated title on public profile', function () {
    $user = User::factory()->create();
    $profile = $user->currentWorkspace()->profile;

    $profile->update([
        'slug' => 'dynamic-spotify-'.uniqid(),
        'is_published' => true,
    ]);

    Link::query()->create([
        'profile_id' => $profile->id,
        'link_type' => 'link',
        'title' => 'Gerade auf Spotify',
        'url' => 'https://open.spotify.com/track/6rqhFgbbKwnb9MLmUQDhG6',
        'preset_key' => 'spotify',
        'provider' => 'spotify',
        'provider_id' => '6rqhFgbbKwnb9MLmUQDhG6',
        'provider_resource_type' => 'track',
        'is_dynamic' => true,
        'cached_title' => 'Song Title',
        'cached_artist' => 'Artist One',
        'position' => 1,
        'is_active' => true,
        'opens_in_new_tab' => false,
        'tracking_enabled' => false,
        'show_icon' => false,
    ]);

    Cache::forget(Profile::publicProfileCacheKey($profile->slug));

    $this->get(route('public.profile', $profile->slug))
        ->assertOk()
        ->assertSee('Song Title', false)
        ->assertSee('Artist One', false);
});
