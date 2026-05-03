<?php

namespace App\Policies;

use App\Models\Profile;
use App\Models\User;

class ProfilePolicy
{
    public function view(User $user, Profile $profile): bool
    {
        return $this->owns($user, $profile);
    }

    public function update(User $user, Profile $profile): bool
    {
        return $this->owns($user, $profile);
    }

    protected function owns(User $user, Profile $profile): bool
    {
        return (int) $profile->workspace->user_id === (int) $user->id;
    }
}
