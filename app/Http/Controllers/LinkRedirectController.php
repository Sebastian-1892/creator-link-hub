<?php

namespace App\Http\Controllers;

use App\Jobs\RecordClickEvent;
use App\Models\Link;
use App\Support\BotDetector;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
class LinkRedirectController extends Controller
{
    public function __invoke(Request $request, Link $link): RedirectResponse
    {
        $link->loadMissing('profile.workspace');

        if (! $link->is_active || ! $link->profile->is_published || $link->profile->workspace->suspended) {
            abort(404);
        }

        $target = $link->url;

        if (! filter_var($target, FILTER_VALIDATE_URL)) {
            abort(404);
        }

        $sessionId = $request->cookie('clh_sid') ?? bin2hex(random_bytes(16));

        $shouldTrack = $link->tracking_enabled
            && ! BotDetector::isLikelyBot($request->userAgent());

        if ($shouldTrack) {
            $ip = $request->ip() ?? '';
            $salt = config('app.key');
            $ipHash = hash_hmac('sha256', $ip, (string) $salt);

            RecordClickEvent::dispatch(
                $link->id,
                $sessionId,
                $ipHash,
                $request->userAgent(),
                null
            );
        }

        $response = redirect()->away($target, 302);

        if (! $request->cookie('clh_sid')) {
            $response->cookie('clh_sid', $sessionId, 60 * 24 * 90, '/', null, true, true, false, 'Lax');
        }

        return $response;
    }
}
