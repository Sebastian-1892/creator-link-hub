<?php

namespace App\Observers;

use App\Models\Link;
use App\Models\Profile;

class LinkObserver
{
    public function saved(Link $link): void
    {
        Profile::forgetPublicProfileCacheForProfileId((int) $link->profile_id);
    }

    public function deleted(Link $link): void
    {
        Profile::forgetPublicProfileCacheForProfileId((int) $link->profile_id);
    }
}
