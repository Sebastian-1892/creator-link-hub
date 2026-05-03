<?php

namespace App\Policies;

use App\Models\Link;
use App\Models\User;

class LinkPolicy
{
    public function update(User $user, Link $link): bool
    {
        return (int) $link->profile->workspace->user_id === (int) $user->id;
    }

    public function delete(User $user, Link $link): bool
    {
        return (int) $link->profile->workspace->user_id === (int) $user->id;
    }
}
