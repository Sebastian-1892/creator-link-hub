<?php

namespace App\Observers;

use App\Models\Profile;
use Illuminate\Support\Facades\Cache;

class ProfileObserver
{
    public function saved(Profile $profile): void
    {
        Cache::forget('pub_profile_v1_'.$profile->slug);
    }

    public function updating(Profile $profile): void
    {
        if ($profile->isDirty('slug')) {
            Cache::forget('pub_profile_v1_'.$profile->getOriginal('slug'));
        }
    }

    public function deleted(Profile $profile): void
    {
        Cache::forget('pub_profile_v1_'.$profile->slug);
    }
}
