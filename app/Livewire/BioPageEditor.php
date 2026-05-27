<?php

namespace App\Livewire;

use App\Models\Profile;
use App\Models\Theme;
use App\Services\PlanService;
use App\Services\SlugService;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class BioPageEditor extends Component
{
    public Profile $profile;

    public string $display_name = '';

    public string $slug = '';

    public string $bio = '';

    public ?int $theme_id = null;

    /** @var 'all'|'light'|'dark'|'colorful'|'minimal' */
    public string $theme_filter = 'all';

    public bool $is_published = false;

    public bool $show_platform_branding = true;

    public ?string $saveNotice = null;

    public function mount(): void
    {
        $workspace = auth()->user()?->currentWorkspace();
        abort_if(! $workspace || ! $workspace->profile, 404);

        $this->profile = $workspace->profile;
        $this->authorize('update', $this->profile);

        $this->display_name = $this->profile->display_name;
        $this->slug = $this->profile->slug;
        $this->bio = (string) $this->profile->bio;
        $this->theme_id = $this->profile->theme_id;
        $this->is_published = $this->profile->is_published;
        $this->show_platform_branding = $this->profile->show_platform_branding;
    }

    public function updatedThemeId(mixed $value): void
    {
        $this->theme_id = ($value === '' || $value === null) ? null : (int) $value;
    }

    public function save(SlugService $slugService, PlanService $plans): void
    {
        $workspace = $this->profile->workspace;
        if ($workspace && ! $plans->canControlPlatformBranding($workspace)) {
            $this->show_platform_branding = true;
        }

        $this->validate([
            'display_name' => ['required', 'string', 'max:120'],
            'slug' => [
                'required',
                'string',
                'max:64',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('profiles', 'slug')->ignore($this->profile->id),
                function (string $attribute, mixed $value, \Closure $fail) use ($slugService): void {
                    if ($slugService->isReserved((string) $value)) {
                        $fail(__('Diese URL ist reserviert.'));
                    }
                },
            ],
            'bio' => ['nullable', 'string', 'max:2000'],
            'theme_id' => ['nullable', 'exists:themes,id'],
            'is_published' => ['boolean'],
            'show_platform_branding' => ['boolean'],
        ]);

        $this->profile->display_name = $this->display_name;
        $this->profile->slug = strtolower($this->slug);
        $this->profile->bio = $this->bio;
        $this->profile->theme_id = $this->theme_id;
        $this->profile->is_published = $this->is_published;
        $this->profile->show_platform_branding = $this->show_platform_branding;
        if ($this->is_published && ! $this->profile->published_at) {
            $this->profile->published_at = now();
        }
        if (! $this->is_published) {
            $this->profile->published_at = null;
        }
        $this->profile->save();

        $this->profile->refresh();

        $this->saveNotice = __('Gespeichert — deine Bio-Seite wurde aktualisiert.');

        $this->js('window.scrollTo({ top: 0, behavior: "smooth" })');
    }

    public function dismissSaveNotice(): void
    {
        $this->saveNotice = null;
    }

    public function render(PlanService $plans)
    {
        $query = Theme::query()->orderBy('name');

        if ($this->theme_filter !== 'all') {
            $query->where('template_group', $this->theme_filter);
        }

        $workspace = $this->profile->workspace;

        return view('livewire.bio-page-editor', [
            'themes' => $query->get(),
            'canControlPlatformBranding' => $workspace ? $plans->canControlPlatformBranding($workspace) : false,
        ]);
    }
}
