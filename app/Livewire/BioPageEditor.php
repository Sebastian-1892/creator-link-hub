<?php

namespace App\Livewire;

use App\Models\Profile;
use App\Models\Theme;
use App\Services\SlugService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class BioPageEditor extends Component
{
    use WithFileUploads;

    public Profile $profile;

    public string $display_name = '';

    public string $slug = '';

    public string $bio = '';

    public ?int $theme_id = null;

    public bool $is_published = false;

    public $avatar;

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
    }

    public function updatedThemeId(mixed $value): void
    {
        $this->theme_id = ($value === '' || $value === null) ? null : (int) $value;
    }

    public function save(SlugService $slugService): void
    {
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
            'avatar' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($this->avatar) {
            $path = $this->avatar->store('avatars', 'public');
            if ($this->profile->avatar_path) {
                Storage::disk('public')->delete($this->profile->avatar_path);
            }
            $this->profile->avatar_path = $path;
        }

        $this->profile->display_name = $this->display_name;
        $this->profile->slug = strtolower($this->slug);
        $this->profile->bio = $this->bio;
        $this->profile->theme_id = $this->theme_id;
        $this->profile->is_published = $this->is_published;
        if ($this->is_published && ! $this->profile->published_at) {
            $this->profile->published_at = now();
        }
        if (! $this->is_published) {
            $this->profile->published_at = null;
        }
        $this->profile->save();

        $this->avatar = null;
        $this->profile->refresh();

        session()->flash('status', __('Gespeichert.'));
    }

    public function render()
    {
        return view('livewire.bio-page-editor', [
            'themes' => Theme::query()->orderBy('name')->get(),
        ]);
    }
}
