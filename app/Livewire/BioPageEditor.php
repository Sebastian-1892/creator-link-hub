<?php

namespace App\Livewire;

use App\Models\Profile;
use App\Models\Theme;
use App\Services\AvatarImageProcessor;
use App\Services\SlugService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\FileUploadConfiguration;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class BioPageEditor extends Component
{
    use WithFileUploads {
        _uploadErrored as protected traitUploadErrored;
    }

    public Profile $profile;

    public string $display_name = '';

    public string $slug = '';

    public string $bio = '';

    public ?int $theme_id = null;

    /** @var 'all'|'light'|'dark'|'colorful'|'minimal' */
    public string $theme_filter = 'all';

    public bool $is_published = false;

    public $avatar;

    public ?string $saveNotice = null;

    public bool $avatarUploadStorageReady = true;

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

        $this->avatarUploadStorageReady = $this->livewireTempDirectoryIsWritable();
    }

    public function updatedThemeId(mixed $value): void
    {
        $this->theme_id = ($value === '' || $value === null) ? null : (int) $value;
    }

    public function updatedAvatar(): void
    {
        $this->resetErrorBag('avatar');

        if (! $this->avatar) {
            return;
        }

        $this->validateOnly('avatar', $this->avatarRules(), $this->avatarMessages());
    }

    public function _uploadErrored($name, $errorsInJson, $isMultiple): void
    {
        if ($name === 'avatar') {
            $this->avatar = null;

            throw ValidationException::withMessages(
                $errorsInJson === null
                    ? ['avatar' => $this->avatarUploadFailureMessage()]
                    : $this->avatarMessagesFromLivewireJson($errorsInJson)
            );
        }

        $this->traitUploadErrored($name, $errorsInJson, $isMultiple);
    }

    public function save(SlugService $slugService, AvatarImageProcessor $avatarProcessor): void
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
            'avatar' => $this->avatarRules(),
        ], $this->avatarMessages());

        if ($this->avatar) {
            try {
                $path = $avatarProcessor->storeFromUpload($this->avatar);
            } catch (\Throwable $e) {
                report($e);

                throw ValidationException::withMessages([
                    'avatar' => __('Das Profilbild konnte nicht verarbeitet werden. Bitte ein anderes Bild (JPG oder PNG) versuchen.'),
                ]);
            }

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

        $this->saveNotice = __('Gespeichert — deine Bio-Seite wurde aktualisiert.');

        $this->js('window.scrollTo({ top: 0, behavior: "smooth" })');
    }

    public function dismissSaveNotice(): void
    {
        $this->saveNotice = null;
    }

    public function render()
    {
        $query = Theme::query()->orderBy('name');

        if ($this->theme_filter !== 'all') {
            $query->where('template_group', $this->theme_filter);
        }

        return view('livewire.bio-page-editor', [
            'themes' => $query->get(),
        ]);
    }

    /**
     * @return list<string|array<int, string>>
     */
    protected function avatarRules(): array
    {
        $maxKb = (int) config('creator.avatar.max_upload_kb', 8192);

        return ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:'.$maxKb];
    }

    /**
     * @return array<string, string>
     */
    protected function avatarMessages(): array
    {
        return [
            'avatar.image' => __('Bitte eine Bilddatei wählen (JPG, PNG, GIF oder WebP). iPhone-Fotos im HEIC-Format werden nicht unterstützt — speichere das Bild zuerst als JPG.'),
            'avatar.mimes' => __('Erlaubte Formate: JPG, PNG, GIF oder WebP.'),
            'avatar.max' => __('Die Datei ist zu groß (maximal 8 MB). Das Bild wird beim Speichern automatisch verkleinert.'),
            'avatar.uploaded' => __('Das Profilbild konnte nicht hochgeladen werden. Bitte Dateigröße und Format prüfen.'),
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function avatarMessagesFromLivewireJson(string $errorsInJson): array
    {
        $messages = $this->avatarMessages();
        $errors = json_decode($errorsInJson, true)['errors'] ?? [];
        $combined = strtolower(implode(' ', array_map(
            fn ($msgs) => implode(' ', (array) $msgs),
            is_array($errors) ? $errors : []
        )));

        if (str_contains($combined, 'max')) {
            return ['avatar' => $messages['avatar.max']];
        }

        if (str_contains($combined, 'mimes') || str_contains($combined, 'image')) {
            return ['avatar' => $messages['avatar.image']];
        }

        return ['avatar' => $this->avatarUploadFailureMessage()];
    }

    protected function avatarUploadFailureMessage(): string
    {
        $hints = [];

        if (! $this->avatarUploadStorageReady) {
            $hints[] = __('Der Upload-Speicher auf dem Server ist nicht beschreibbar. Bitte den Support kontaktieren.');
        }

        if (! $this->appUrlMatchesCurrentRequest()) {
            $hints[] = __('Die Seiten-Adresse passt nicht zur Server-Konfiguration — lade die Seite neu (F5). Bleibt der Fehler, melde dich beim Support.');
        }

        if ($hints !== []) {
            return implode(' ', $hints);
        }

        return __('Das Profilbild konnte nicht hochgeladen werden. Erlaubt: JPG, PNG, GIF oder WebP (bis 8 MB). Große Bilder werden nach dem Upload automatisch verkleinert. iPhone-HEIC bitte zuerst als JPG speichern.');
    }

    protected function livewireTempDirectoryIsWritable(): bool
    {
        try {
            $disk = Storage::disk(FileUploadConfiguration::disk());
            $probe = FileUploadConfiguration::path('.clh-write-test');
            $disk->put($probe, '1');
            $disk->delete($probe);

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    protected function appUrlMatchesCurrentRequest(): bool
    {
        if (! app()->runningInConsole() && request()->hasHeader('Host')) {
            $configured = rtrim((string) config('app.url'), '/');
            $actual = request()->getSchemeAndHttpHost();

            return strcasecmp($configured, $actual) === 0;
        }

        return true;
    }
}
