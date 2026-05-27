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

    public string $collectionTitle = '';

    public ?int $productCollectionId = null;

    public string $productTitle = '';

    public string $productUrl = '';

    public string $productImageUrl = '';

    /** @var array<int, string> */
    public array $linkTitles = [];

    /** @var array<int, bool> */
    public array $linkShowIcons = [];

    public function mount(): void
    {
        $workspace = auth()->user()?->currentWorkspace();
        abort_if(! $workspace || ! $workspace->profile, 404);

        $this->profile = $workspace->profile->load(['links' => fn ($q) => $q->orderBy('position')]);
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

    public function openCollectionModal(): void
    {
        $this->reset('collectionTitle');
        $this->resetErrorBag();
        $this->dispatch('open-modal', 'add-collection');
    }

    public function createCollection(PlanService $plans): void
    {
        $this->validate([
            'collectionTitle' => ['required', 'string', 'max:120'],
        ]);

        $workspace = $this->profile->workspace;
        if (! $plans->canAddLink($workspace, $this->profile)) {
            session()->flash('error', __('Im Free-Plan sind maximal :n Links möglich.', ['n' => config('creator.free_link_limit')]));
            $this->dispatch('close-modal', 'add-collection');

            return;
        }

        $maxPos = (int) $this->profile->links()->whereNull('parent_link_id')->max('position');

        Link::query()->create([
            'profile_id' => $this->profile->id,
            'link_type' => 'collection',
            'parent_link_id' => null,
            'title' => $this->collectionTitle,
            'url' => '#',
            'image_url' => null,
            'preset_key' => 'custom',
            'show_icon' => false,
            'position' => $maxPos + 1,
            'is_active' => true,
            'opens_in_new_tab' => false,
            'tracking_enabled' => false,
        ]);

        $this->profile->refresh()->load(['links' => fn ($q) => $q->orderBy('position')]);
        $this->syncLinkFormState();
        $this->dispatch('close-modal', 'add-collection');
    }

    public function openProductModal(int $collectionId): void
    {
        $collection = Link::query()
            ->where('profile_id', $this->profile->id)
            ->where('link_type', 'collection')
            ->findOrFail($collectionId);

        $this->authorize('update', $collection);

        $this->productCollectionId = $collection->id;
        $this->reset('productTitle', 'productUrl', 'productImageUrl');
        $this->resetErrorBag();
        $this->dispatch('open-modal', 'add-product');
    }

    public function createProduct(PlanService $plans): void
    {
        $this->validate([
            'productCollectionId' => ['required', 'integer'],
            'productTitle' => ['required', 'string', 'max:120'],
            'productUrl' => ['required', 'string', 'max:2048', 'url'],
            'productImageUrl' => ['nullable', 'string', 'max:2048', 'url'],
        ]);

        $collection = Link::query()
            ->where('profile_id', $this->profile->id)
            ->where('link_type', 'collection')
            ->findOrFail($this->productCollectionId);

        $this->authorize('update', $collection);

        $workspace = $this->profile->workspace;
        if (! $plans->canAddLink($workspace, $this->profile)) {
            session()->flash('error', __('Im Free-Plan sind maximal :n Links möglich.', ['n' => config('creator.free_link_limit')]));
            $this->dispatch('close-modal', 'add-product');

            return;
        }

        $maxPos = (int) $this->profile->links()
            ->where('parent_link_id', $collection->id)
            ->max('position');

        Link::query()->create([
            'profile_id' => $this->profile->id,
            'link_type' => 'product',
            'parent_link_id' => $collection->id,
            'title' => $this->productTitle,
            'url' => $this->productUrl,
            'image_url' => $this->productImageUrl !== '' ? $this->productImageUrl : null,
            'preset_key' => 'custom',
            'show_icon' => false,
            'position' => $maxPos + 1,
            'is_active' => true,
            'opens_in_new_tab' => true,
            'tracking_enabled' => true,
        ]);

        $this->profile->refresh()->load(['links' => fn ($q) => $q->orderBy('position')]);
        $this->syncLinkFormState();
        $this->dispatch('close-modal', 'add-product');
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
        $this->profile->refresh()->load(['links' => fn ($q) => $q->orderBy('position')]);
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
        $this->profile->refresh()->load(['links' => fn ($q) => $q->orderBy('position')]);
    }

    public function deleteLink(int $linkId): void
    {
        $link = Link::query()->where('profile_id', $this->profile->id)->findOrFail($linkId);
        $this->authorize('delete', $link);
        $link->delete();
        $this->profile->refresh()->load(['links' => fn ($q) => $q->orderBy('position')]);
        $this->syncLinkFormState();
    }

    public function move(int $linkId, string $direction): void
    {
        $current = $this->profile->links()->findOrFail($linkId);
        $links = $this->profile->links()
            ->where('parent_link_id', $current->parent_link_id)
            ->orderBy('position')
            ->get()
            ->values();
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

        $this->profile->refresh()->load(['links' => fn ($q) => $q->orderBy('position')]);
    }

    public function render()
    {
        $links = $this->profile->links()->orderBy('position')->get();

        return view('livewire.link-manager', [
            'links' => $links->whereNull('parent_link_id')->values(),
            'productsByCollection' => $links
                ->whereNotNull('parent_link_id')
                ->where('link_type', 'product')
                ->filter(fn (Link $product): bool => $links->contains('id', $product->parent_link_id))
                ->groupBy('parent_link_id'),
            'linkPresets' => LinkPresetHelper::presets(),
        ]);
    }

    protected function syncLinkFormState(): void
    {
        $this->linkTitles = [];
        $this->linkShowIcons = [];

        foreach ($this->profile->links()->orderBy('position')->get() as $link) {
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

        $defaultLabel = __('presets.'.($preset['key'] ?? 'custom'));
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

        $maxPos = (int) $this->profile->links()->whereNull('parent_link_id')->max('position');

        Link::query()->create([
            'profile_id' => $this->profile->id,
            'link_type' => 'link',
            'parent_link_id' => null,
            'title' => $title,
            'url' => $url,
            'image_url' => null,
            'preset_key' => $resolvedPreset,
            'show_icon' => $showIcon,
            'position' => $maxPos + 1,
            'is_active' => true,
            'opens_in_new_tab' => true,
            'tracking_enabled' => true,
        ]);

        $this->profile->refresh()->load(['links' => fn ($q) => $q->orderBy('position')]);
        $this->syncLinkFormState();
    }

    protected function resetAfterAdd(): void
    {
        $this->clearPreset();
        $this->dispatch('close-modal', 'add-link');
    }
}
