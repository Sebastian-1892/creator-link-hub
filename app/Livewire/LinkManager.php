<?php

namespace App\Livewire;

use App\Models\Link;
use App\Models\Profile;
use App\Services\PlanService;
use App\Support\LinkPresetHelper;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class LinkManager extends Component
{
    public Profile $profile;

    public ?string $presetKey = null;

    public string $presetValue = '';

    public string $newTitle = '';

    public string $newUrl = '';

    /** @var array<int, string> */
    public array $linkTitles = [];

    /** @var array<int, bool> */
    public array $linkShowIcons = [];

    public function mount(): void
    {
        $workspace = auth()->user()?->currentWorkspace();
        abort_if(! $workspace || ! $workspace->profile, 404);

        $this->profile = $workspace->profile->load('links');
        $this->authorize('update', $this->profile);
        $this->syncLinkFormState();
    }

    public function selectPreset(string $key): void
    {
        abort_unless(array_key_exists($key, LinkPresetHelper::presets()), 404);

        $this->presetKey = $key;
        $this->reset('presetValue', 'newTitle', 'newUrl');
        $this->resetErrorBag();
    }

    public function clearPreset(): void
    {
        $this->presetKey = null;
        $this->reset('presetValue', 'newTitle', 'newUrl');
        $this->resetErrorBag();
    }

    public function closeAddModal(): void
    {
        $this->clearPreset();
        $this->dispatch('close-modal', 'add-link');
    }

    public function addPresetLink(PlanService $plans): void
    {
        if ($this->presetKey === null) {
            return;
        }

        $preset = LinkPresetHelper::preset($this->presetKey);

        if ($preset === null) {
            return;
        }

        [$title, $url] = $this->resolvePresetTitleAndUrl($preset);

        $this->createLinkFromInput(
            $title,
            $url,
            $plans,
            $this->presetKey === 'custom' ? null : $this->presetKey,
        );
        $this->resetAfterAdd();
    }

    public function addLink(PlanService $plans): void
    {
        $this->validate([
            'newTitle' => ['required', 'string', 'max:120'],
            'newUrl' => ['required', 'string', 'max:2048', 'url'],
        ]);

        $this->createLinkFromInput($this->newTitle, $this->newUrl, $plans, 'custom');
        $this->resetAfterAdd();
    }

    public function updatedLinkTitles(mixed $value, string $key): void
    {
        $linkId = (int) $key;
        $link = Link::query()->where('profile_id', $this->profile->id)->find($linkId);

        if (! $link) {
            return;
        }

        $this->authorize('update', $link);

        $validated = $this->validate([
            "linkTitles.{$linkId}" => ['required', 'string', 'max:120'],
        ]);

        $link->update(['title' => $validated['linkTitles'][$linkId]]);
        $this->profile->refresh()->load('links');
    }

    public function updatedLinkShowIcons(mixed $value, string $key): void
    {
        $linkId = (int) $key;
        $link = Link::query()->where('profile_id', $this->profile->id)->find($linkId);

        if (! $link) {
            return;
        }

        $this->authorize('update', $link);

        $link->update(['show_icon' => (bool) $value]);
        $this->profile->refresh()->load('links');
    }

    public function deleteLink(int $linkId): void
    {
        $link = Link::query()->where('profile_id', $this->profile->id)->findOrFail($linkId);
        $this->authorize('delete', $link);
        $link->delete();
        $this->profile->refresh()->load('links');
        $this->syncLinkFormState();
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
            'linkPresets' => LinkPresetHelper::presets(),
        ]);
    }

    protected function syncLinkFormState(): void
    {
        $this->linkTitles = [];
        $this->linkShowIcons = [];

        foreach ($this->profile->links as $link) {
            $this->linkTitles[$link->id] = $link->title;
            $this->linkShowIcons[$link->id] = $link->show_icon;
        }
    }

    /**
     * @param  array<string, mixed>  $preset
     * @return array{0: string, 1: string}
     */
    protected function resolvePresetTitleAndUrl(array $preset): array
    {
        $type = $preset['type'] ?? 'custom';

        if ($type === 'custom') {
            $this->validate([
                'newTitle' => ['required', 'string', 'max:120'],
                'newUrl' => ['required', 'string', 'max:2048', 'url'],
            ]);

            return [$this->newTitle, $this->newUrl];
        }

        $rules = match ($type) {
            'username' => ['presetValue' => ['required', 'string', 'regex:/^[A-Za-z0-9._-]{1,64}$/']],
            'url' => ['presetValue' => ['required', 'string', 'max:2048', 'url']],
            'email' => ['presetValue' => ['required', 'string', 'email', 'max:255']],
            'phone' => ['presetValue' => ['required', 'string', 'regex:/^\+?[0-9]{6,20}$/']],
            default => throw ValidationException::withMessages([
                'presetValue' => __('Unbekannter Link-Typ.'),
            ]),
        };

        $this->validate($rules, [
            'presetValue.required' => __('Bitte einen Wert eingeben.'),
            'presetValue.regex' => __('Ungültiges Format — bitte prüfen und erneut versuchen.'),
            'presetValue.url' => __('Bitte eine gültige URL eingeben.'),
            'presetValue.email' => __('Bitte eine gültige E-Mail-Adresse eingeben.'),
        ]);

        $value = trim($this->presetValue);

        if ($type === 'phone') {
            $value = preg_replace('/\D/', '', $value) ?? $value;
        }

        if ($type === 'username') {
            $value = ltrim($value, '@');
        }

        $template = $preset['url_template'] ?? '{value}';
        $url = str_replace('{value}', $value, $template);

        $defaultLabel = $preset['label'] ?? __('Link');
        $title = trim($this->newTitle) !== '' ? trim($this->newTitle) : $defaultLabel;

        return [$title, $url];
    }

    protected function createLinkFromInput(
        string $title,
        string $url,
        PlanService $plans,
        ?string $presetKey = null,
        bool $showIcon = true,
    ): void {
        $workspace = $this->profile->workspace;

        if (! $plans->canAddLink($workspace, $this->profile)) {
            session()->flash('error', __('Im Free-Plan sind maximal :n Links möglich.', ['n' => config('creator.free_link_limit')]));
            $this->dispatch('close-modal', 'add-link');

            return;
        }

        $resolvedPreset = $presetKey ?? LinkPresetHelper::detectFromUrl($url);

        $maxPos = (int) $this->profile->links()->max('position');

        Link::query()->create([
            'profile_id' => $this->profile->id,
            'title' => $title,
            'url' => $url,
            'preset_key' => $resolvedPreset,
            'show_icon' => $showIcon,
            'position' => $maxPos + 1,
            'is_active' => true,
            'opens_in_new_tab' => true,
            'tracking_enabled' => true,
        ]);

        $this->profile->refresh()->load('links');
        $this->syncLinkFormState();
    }

    protected function resetAfterAdd(): void
    {
        $this->clearPreset();
        $this->dispatch('close-modal', 'add-link');
    }
}
