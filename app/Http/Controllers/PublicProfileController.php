<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Services\PlanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class PublicProfileController extends Controller
{
    public function show(Request $request, string $slug, PlanService $plans): View
    {
        $profile = Cache::remember(
            Profile::publicProfileCacheKey($slug),
            now()->addDay(),
            function () use ($slug) {
                return Profile::query()
                    ->where('slug', $slug)
                    ->with([
                        'theme',
                        'workspace',
                        'links' => fn ($q) => $q->where('is_active', true)->orderBy('position'),
                    ])
                    ->firstOrFail();
            }
        );

        if (! $profile->is_published) {
            // Kein 404 mehr bei „nicht veröffentlicht“ (Template-Auswahl / Vorschau soll nicht als Fehler wirken).
            // Inhalte werden bewusst nicht öffentlich gezeigt.
            $safeProfile = clone $profile;
            $safeProfile->display_name = 'Seite nicht veröffentlicht';
            $safeProfile->bio = '';
            $safeProfile->avatar_path = null;
            $safeProfile->setRelation('links', collect());

            return view('public.profile-unpublished', [
                'profile' => $safeProfile,
                'showPlatformBranding' => false,
            ]);
        }

        return view('public.profile', [
            'profile' => $profile,
            'showPlatformBranding' => $plans->showsPlatformBranding($profile->workspace),
        ]);
    }
}
