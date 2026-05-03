<?php

namespace App\Observers;

use App\Models\Profile;
use Illuminate\Support\Facades\Cache;

class ProfileObserver
{
    public function saved(Profile $profile): void
    {
        Cache::forget(Profile::publicProfileCacheKey($profile->slug));
    }

    public function updating(Profile $profile): void
    {
        if ($profile->isDirty('slug')) {
            $old = $profile->getOriginal('slug');
            if (is_string($old) && $old !== '') {
                Cache::forget(Profile::publicProfileCacheKey($old));
            }
        }
    }

    public function deleted(Profile $profile): void
    {
        Cache::forget(Profile::publicProfileCacheKey($profile->slug));
    }
}
