<?php

namespace App\Livewire;

use App\Models\Link;
use App\Models\Profile;
use App\Services\PlanService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class LinkManager extends Component
{
    public Profile $profile;

    public string $newTitle = '';

    public string $newUrl = '';

    public function mount(): void
    {
        $workspace = auth()->user()?->currentWorkspace();
        abort_if(! $workspace || ! $workspace->profile, 404);

        $this->profile = $workspace->profile->load('links');
        $this->authorize('update', $this->profile);
    }

    public function addLink(PlanService $plans): void
    {
        $workspace = $this->profile->workspace;

        if (! $plans->canAddLink($workspace, $this->profile)) {
            session()->flash('error', __('Im Free-Plan sind maximal :n Links möglich.', ['n' => config('creator.free_link_limit')]));

            return;
        }

        $this->validate([
            'newTitle' => ['required', 'string', 'max:120'],
            'newUrl' => ['required', 'string', 'max:2048', 'url'],
        ]);

        $maxPos = (int) $this->profile->links()->max('position');

        Link::query()->create([
            'profile_id' => $this->profile->id,
            'title' => $this->newTitle,
            'url' => $this->newUrl,
            'position' => $maxPos + 1,
            'is_active' => true,
            'opens_in_new_tab' => true,
            'tracking_enabled' => true,
        ]);

        $this->reset('newTitle', 'newUrl');
        $this->profile->refresh()->load('links');
    }

    public function deleteLink(int $linkId): void
    {
        $link = Link::query()->where('profile_id', $this->profile->id)->findOrFail($linkId);
        $this->authorize('delete', $link);
        $link->delete();
        $this->profile->refresh()->load('links');
    }

    public function move(int $linkId, string $direction): void
    {
        $links = $this->profile->links()->orderBy('position')->get()->values();
        $index = $links->search(fn ($l) => $l->id === $linkId);

        if ($index === false) {
            return;
        }

        $swapWith = $direction === 'up' ? $index - 1 : $index + 1;

        if ($swapWith < 0 || $swapWith >= $links->count()) {
            return;
        }

        $a = $links[$index];
        $b = $links[$swapWith];

        $tmp = $a->position;
        $a->position = $b->position;
        $b->position = $tmp;
        $a->save();
        $b->save();

        $this->profile->refresh()->load('links');
    }

    public function render()
    {
        return view('livewire.link-manager', [
            'links' => $this->profile->links()->orderBy('position')->get(),
        ]);
    }
}
