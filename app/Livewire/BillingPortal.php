<?php

namespace App\Livewire;

use App\Services\PlanService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class BillingPortal extends Component
{
    public function mount(PlanService $planService): void
    {
        $user = auth()->user();
        $planService->syncWorkspacePlanFromStripe($user);
    }

    public function checkout(string $plan, PlanService $planService)
    {
        $user = auth()->user();

        $priceId = config('creator.stripe_prices.'.$plan);

        if (! $priceId) {
            session()->flash('error', __('Stripe-Preis-ID fehlt in .env (STRIPE_PRICE_…).'));

            return;
        }

        return $user->newSubscription('default', $priceId)
            ->checkout([
                'success_url' => route('billing', absolute: true).'?checkout=success',
                'cancel_url' => route('billing', absolute: true),
            ]);
    }

    public function portal()
    {
        $user = auth()->user();

        if (! $user->stripe_id) {
            session()->flash('error', __('Noch kein Stripe-Kundenkonto — starte ein Abo.'));

            return;
        }

        return $user->redirectToBillingPortal(route('billing'));
    }

    public function render(PlanService $planService)
    {
        $user = auth()->user();
        $workspace = $user->currentWorkspace();

        $planService->syncWorkspacePlanFromStripe($user);

        return view('livewire.billing-portal', [
            'workspace' => $workspace,
            'subscription' => $user->subscription('default'),
        ]);
    }
}
