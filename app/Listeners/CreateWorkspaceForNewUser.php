<?php

namespace App\Listeners;

use App\Models\User;
use App\Services\WorkspaceProvisioner;
use Illuminate\Auth\Events\Registered;

class CreateWorkspaceForNewUser
{
    public function __construct(
        protected WorkspaceProvisioner $provisioner
    ) {}

    public function handle(Registered $event): void
    {
        $user = $event->user;

        if (! $user instanceof User) {
            return;
        }

        $this->provisioner->provisionForUser($user);
    }
}
