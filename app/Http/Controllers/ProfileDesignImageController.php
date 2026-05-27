<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Services\ProfileImageProcessor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileDesignImageController extends Controller
{
    public function storeWallpaper(Request $request, ProfileImageProcessor $processor): RedirectResponse
    {
        $profile = $this->profileForUser($request);
        $this->authorize('update', $profile);

        $request->validate([
            'wallpaper' => ['required', 'image', 'max:'.(int) config('creator.profile_images.max_upload_kb', 8192)],
        ]);

        if ($profile->wallpaper_image_path) {
            Storage::disk('public')->delete($profile->wallpaper_image_path);
        }

        $path = $processor->storeFromUpload($request->file('wallpaper'), 'wallpapers');

        $vars = is_array($profile->theme_variables) ? $profile->theme_variables : [];
        $wallpaper = is_array($vars['wallpaper'] ?? null) ? $vars['wallpaper'] : [];
        $wallpaper['style'] = 'image';
        $vars['wallpaper'] = $wallpaper;

        $profile->update([
            'wallpaper_image_path' => $path,
            'theme_variables' => $vars,
        ]);

        return redirect()
            ->route('design.edit', ['section' => 'wallpaper'])
            ->with('design_notice', __('Hintergrundbild hochgeladen.'));
    }

    public function destroyWallpaper(Request $request): RedirectResponse
    {
        $profile = $this->profileForUser($request);
        $this->authorize('update', $profile);

        if ($profile->wallpaper_image_path) {
            Storage::disk('public')->delete($profile->wallpaper_image_path);
        }

        $profile->update(['wallpaper_image_path' => null]);

        return redirect()
            ->route('design.edit', ['section' => 'wallpaper'])
            ->with('design_notice', __('Hintergrundbild entfernt.'));
    }

    public function storeBanner(Request $request, ProfileImageProcessor $processor): RedirectResponse
    {
        $profile = $this->profileForUser($request);
        $this->authorize('update', $profile);

        $request->validate([
            'banner' => ['required', 'image', 'max:'.(int) config('creator.profile_images.max_upload_kb', 8192)],
        ]);

        if ($profile->banner_image_path) {
            Storage::disk('public')->delete($profile->banner_image_path);
        }

        $path = $processor->storeFromUpload($request->file('banner'), 'banners');

        $profile->update([
            'banner_image_path' => $path,
            'header_layout' => 'banner',
        ]);

        return redirect()
            ->route('design.edit', ['section' => 'header'])
            ->with('design_notice', __('Banner-Bild hochgeladen.'));
    }

    public function destroyBanner(Request $request): RedirectResponse
    {
        $profile = $this->profileForUser($request);
        $this->authorize('update', $profile);

        if ($profile->banner_image_path) {
            Storage::disk('public')->delete($profile->banner_image_path);
        }

        $profile->update(['banner_image_path' => null]);

        return redirect()
            ->route('design.edit', ['section' => 'header'])
            ->with('design_notice', __('Banner-Bild entfernt.'));
    }

    private function profileForUser(Request $request): Profile
    {
        $workspace = $request->user()?->currentWorkspace();
        abort_if(! $workspace || ! $workspace->profile, 404);

        return $workspace->profile;
    }
}
