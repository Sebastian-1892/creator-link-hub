<?php

namespace App\Services;

use App\Models\Profile;
use App\Models\User;
use App\Models\Workspace;

class PlanService
{
    public function maxLinksForWorkspace(Workspace $workspace): ?int
    {
        $plan = $workspace->plan;

        return config("creator.plans.{$plan}.link_limit")
            ?? ($plan === 'free' ? config('creator.free_link_limit') : null);
    }

    public function canAddLink(Workspace $workspace, Profile $profile): bool
    {
        $max = $this->maxLinksForWorkspace($workspace);

        if ($max === null) {
            return true;
        }

        return $profile->links()->count() < $max;
    }

    public function showsPlatformBranding(Workspace $workspace): bool
    {
        $plan = $workspace->plan;

        return (bool) (config("creator.plans.{$plan}.platform_branding") ?? true);
    }

    public function syncWorkspacePlanFromSubscription(Workspace $workspace, string $planKey): void
    {
        if (! in_array($planKey, ['free', 'starter', 'pro', 'business'], true)) {
            return;
        }

        $workspace->forceFill(['plan' => $planKey === 'business' ? 'pro' : $planKey])->save();
    }

    public function syncWorkspacePlanFromStripe(User $user): void
    {
        $workspace = $user->currentWorkspace();

        if (! $workspace) {
            return;
        }

        $subscription = $user->subscription('default');

        if (! $subscription || ! $subscription->valid()) {
            $workspace->forceFill(['plan' => 'free'])->save();

            return;
        }

        $stripePrice = $subscription->stripe_price;

        foreach (config('creator.stripe_prices', []) as $planKey => $priceId) {
            if ($priceId && $stripePrice === $priceId) {
                $workspace->forceFill(['plan' => $planKey])->save();

                return;
            }
        }
    }
}
