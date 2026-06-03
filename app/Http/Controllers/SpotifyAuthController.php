<?php

namespace App\Http\Controllers;

use App\Models\SpotifyAccount;
use App\Services\SpotifyOAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class SpotifyAuthController extends Controller
{
    public function connect(Request $request, SpotifyOAuthService $oauth): RedirectResponse
    {
        $workspace = $request->user()?->currentWorkspace();
        abort_if(! $workspace, 404);

        $state = $oauth->generateState();

        $request->session()->put('spotify_oauth_state', $state);
        $request->session()->put('spotify_oauth_workspace_id', $workspace->id);

        return redirect()->away($oauth->authorizationUrl($state));
    }

    public function callback(Request $request, SpotifyOAuthService $oauth): RedirectResponse
    {
        $expectedState = $request->session()->pull('spotify_oauth_state');
        $workspaceId = $request->session()->pull('spotify_oauth_workspace_id');

        abort_if(! is_string($expectedState) || $expectedState === '', 403);
        abort_if(! is_int($workspaceId) && ! is_string($workspaceId), 403);
        abort_if($request->query('state') !== $expectedState, 403);

        $code = $request->query('code');
        abort_if(! is_string($code) || $code === '', 400);

        try {
            $tokens = $oauth->exchangeCode($code);
        } catch (RuntimeException) {
            return redirect()
                ->route('links.manage')
                ->with('error', __('Spotify-Verbindung fehlgeschlagen. Bitte erneut versuchen.'));
        }

        $profileResponse = Http::withToken($tokens['access_token'])
            ->acceptJson()
            ->get('https://api.spotify.com/v1/me');

        if (! $profileResponse->successful()) {
            return redirect()
                ->route('links.manage')
                ->with('error', __('Spotify-Profil konnte nicht geladen werden.'));
        }

        /** @var array{id?: string}|null $profile */
        $profile = $profileResponse->json();
        $spotifyUserId = is_string($profile['id'] ?? null) ? $profile['id'] : null;

        if ($spotifyUserId === null) {
            return redirect()
                ->route('links.manage')
                ->with('error', __('Spotify-Profil konnte nicht geladen werden.'));
        }

        $account = SpotifyAccount::query()->firstOrNew(['workspace_id' => (int) $workspaceId]);
        $account->spotify_user_id = $spotifyUserId;
        $account->setAccessToken($tokens['access_token']);
        $account->setRefreshToken($tokens['refresh_token']);
        $account->expires_at = now()->addSeconds(max(60, $tokens['expires_in'] - 60));
        $account->connection_status = 'connected';
        $account->save();

        return redirect()
            ->route('links.manage')
            ->with('status', __('Spotify erfolgreich verbunden.'));
    }

    public function disconnect(Request $request): RedirectResponse
    {
        $workspace = $request->user()?->currentWorkspace();
        abort_if(! $workspace, 404);

        SpotifyAccount::query()->where('workspace_id', $workspace->id)->delete();

        return redirect()
            ->route('links.manage')
            ->with('status', __('Spotify-Verbindung getrennt.'));
    }
}
