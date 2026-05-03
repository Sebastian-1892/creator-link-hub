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
                    ->where('is_published', true)
                    ->with([
                        'theme',
                        'workspace',
                        'links' => fn ($q) => $q->where('is_active', true)->orderBy('position'),
                    ])
                    ->firstOrFail();
            }
        );

        return view('public.profile', [
            'profile' => $profile,
            'showPlatformBranding' => $plans->showsPlatformBranding($profile->workspace),
        ]);
    }
}
