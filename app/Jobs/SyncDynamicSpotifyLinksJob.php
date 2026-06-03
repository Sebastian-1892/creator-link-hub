<?php

namespace App\Jobs;

use App\Models\Link;
use App\Models\Profile;
use App\Models\SpotifyAccount;
use App\Services\SpotifyApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class SyncDynamicSpotifyLinksJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /** @var list<int> */
    public array $backoff = [30, 120, 300];

    public function handle(SpotifyApiService $spotifyApi): void
    {
        $links = Link::query()
            ->where('is_dynamic', true)
            ->where('provider', 'spotify')
            ->where('is_active', true)
            ->with('profile.workspace.spotifyAccount')
            ->get();

        /** @var array<int, SpotifyAccount> $accountsByWorkspace */
        $accountsByWorkspace = [];

        foreach ($links as $link) {
            $workspace = $link->profile?->workspace;
            $account = $workspace?->spotifyAccount;

            if ($workspace === null || $account === null || ! $account->isConnected()) {
                continue;
            }

            if (! isset($accountsByWorkspace[$workspace->id])) {
                $accountsByWorkspace[$workspace->id] = $account;
            }
        }

        foreach ($accountsByWorkspace as $account) {
            $this->syncWorkspace($account, $spotifyApi);
        }
    }

    private function syncWorkspace(SpotifyAccount $account, SpotifyApiService $spotifyApi): void
    {
        try {
            $playback = $spotifyApi->resolveLatestPlayback($account);
        } catch (RuntimeException $exception) {
            Log::warning('Spotify sync failed for workspace '.$account->workspace_id, [
                'message' => $exception->getMessage(),
            ]);

            if ($account->connection_status === 'revoked') {
                return;
            }

            $account->update(['connection_status' => 'error']);

            return;
        }

        $account->update([
            'last_sync_at' => now(),
            'connection_status' => 'connected',
        ]);

        if ($playback === null) {
            return;
        }

        $dynamicLinks = Link::query()
            ->where('is_dynamic', true)
            ->where('provider', 'spotify')
            ->whereHas('profile', fn ($query) => $query->where('workspace_id', $account->workspace_id))
            ->get();

        foreach ($dynamicLinks as $link) {
            $link->update([
                'provider_resource_type' => $playback['resource_type'],
                'provider_id' => $playback['provider_id'],
                'url' => $playback['canonical_url'],
                'cached_title' => $playback['title'],
                'cached_artist' => $playback['artist'],
                'cached_image' => $playback['image'],
            ]);

            Profile::forgetPublicProfileCacheForProfileId($link->profile_id);
        }
    }
}
