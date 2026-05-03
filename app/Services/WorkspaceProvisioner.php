<?php

namespace App\Services;

use App\Models\Profile;
use App\Models\Theme;
use App\Models\User;
use App\Models\Workspace;

class WorkspaceProvisioner
{
    public function __construct(
        protected SlugService $slugService
    ) {}

    public function provisionForUser(User $user): Workspace
    {
        if ($user->workspaces()->exists()) {
            return $user->workspaces()->first();
        }

        $workspace = Workspace::query()->create([
            'user_id' => $user->id,
            'name' => $user->name.' — Link Hub',
            'plan' => 'free',
        ]);

        $theme = Theme::query()->where('slug', 'minimal-dark')->first()
            ?? Theme::query()->first();

        $slug = $this->slugService->makeUniqueFromBase($user->name);

        Profile::query()->create([
            'workspace_id' => $workspace->id,
            'theme_id' => $theme?->id,
            'slug' => $slug,
            'display_name' => $user->name,
            'bio' => null,
            'is_published' => false,
        ]);

        return $workspace;
    }
}
