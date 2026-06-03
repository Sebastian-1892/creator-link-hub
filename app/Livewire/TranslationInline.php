<?php

namespace App\Livewire;

use App\Models\TranslationString;
use App\Services\BrandingService;
use App\Services\TranslationService;
use Livewire\Attributes\On;
use Livewire\Component;

class TranslationInline extends Component
{
    public bool $open = false;

    public string $translationKey = '';

    public string $value = '';

    public string $format = TranslationString::FORMAT_TEXT;

    public string $locale = '';

    #[On('open-translation-editor')]
    public function openEditor(string $key, string $current = '', string $format = TranslationString::FORMAT_TEXT): void
    {
        if (! $this->canEdit()) {
            return;
        }

        $this->translationKey = $key;
        $this->value = $current;
        $this->format = $format;
        $this->locale = app()->getLocale();
        $this->open = true;
    }

    public function save(): void
    {
        if (! $this->canEdit()) {
            return;
        }

        $this->validate([
            'value' => ['nullable', 'string', 'max:50000'],
        ]);

        app(TranslationService::class)->set(
            $this->locale,
            $this->translationKey,
            $this->value,
            $this->format
        );

        $this->open = false;
        $this->redirect(request()->fullUrl(), navigate: true);
    }

    public function revert(): void
    {
        if (! $this->canEdit()) {
            return;
        }

        $svc = app(TranslationService::class);
        if (! $svc->revert($this->locale, $this->translationKey)) {
            $svc->revertToLangDefault($this->locale, $this->translationKey);
        }

        app(BrandingService::class)->flushPayloadCache();
        $this->open = false;
        $this->redirect(request()->fullUrl(), navigate: true);
    }

    public function close(): void
    {
        $this->open = false;
    }

    protected function canEdit(): bool
    {
        if (! config('creator.i18n_inline_editor', false)) {
            return false;
        }

        $user = auth()->user();

        return $user !== null && (bool) $user->is_admin;
    }

    public function render()
    {
        return view('livewire.translation-inline');
    }
}
