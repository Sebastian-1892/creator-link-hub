<?php

namespace App\Livewire;

use App\Models\PageSection;
use App\Services\HtmlSanitizerService;
use App\Services\PageSectionService;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\On;
use Livewire\Component;

class PageSectionEditor extends Component
{
    public bool $open = false;

    public string $page = 'home';

    public string $sectionKey = '';

    public string $label = '';

    public string $locale = '';

    public string $content = '';

    public bool $isVisible = true;

    #[On('open-page-section-editor')]
    public function openEditor(string $page, string $sectionKey, string $locale = '', string $label = ''): void
    {
        if (! $this->canEdit()) {
            return;
        }

        $this->page = $page;
        $this->sectionKey = $sectionKey;
        $this->locale = $locale !== '' ? $locale : app()->getLocale();
        $this->label = $label;

        $section = app(PageSectionService::class)->find($page, $sectionKey, $this->locale);

        if ($section === null || ! $section->isEditable()) {
            return;
        }

        $this->content = (string) ($section->content ?? '');
        $this->isVisible = $section->is_visible;
        $this->open = true;

        $this->dispatch('page-section-editor-opened', content: $this->content);
    }

    public function save(string $editorHtml = ''): void
    {
        if (! $this->canEdit()) {
            return;
        }

        $key = 'page-section-save:'.auth()->id();
        if (RateLimiter::tooManyAttempts($key, 30)) {
            $this->addError('content', __('Zu viele Speichervorgänge — bitte kurz warten.'));

            return;
        }
        RateLimiter::hit($key, 60);

        $raw = $editorHtml !== '' ? $editorHtml : $this->content;

        $this->validate([
            'page' => ['required', 'string', 'max:64'],
            'sectionKey' => ['required', 'string', 'max:64'],
            'locale' => ['required', 'string', 'max:5'],
        ]);

        if (strlen($raw) > 51200) {
            $this->addError('content', __('Der Inhalt ist zu lang (max. 50 KB).'));

            return;
        }

        $sanitized = app(HtmlSanitizerService::class)->sanitize($raw);

        $registry = config("page-sections.pages.{$this->page}.{$this->sectionKey}");
        if (! is_array($registry) || ($registry['render_type'] ?? 'html') !== 'html') {
            return;
        }

        $row = PageSection::query()->firstOrNew([
            'page' => $this->page,
            'section_key' => $this->sectionKey,
            'locale' => $this->locale,
        ]);

        $row->previous_content = $row->content;
        $row->content = $sanitized;
        $row->is_visible = $this->isVisible;
        $row->sort_order = (int) ($registry['sort_order'] ?? $row->sort_order ?? 0);
        $row->updated_by_user_id = auth()->id();
        $row->updated_at = now();
        $row->save();

        app(PageSectionService::class)->flushCache($this->page, $this->locale);

        $this->content = $sanitized;
        $this->open = false;

        $this->dispatch('page-section-updated', [
            'sectionKey' => $this->sectionKey,
            'html' => $sanitized,
        ]);
    }

    public function revert(): void
    {
        if (! $this->canEdit()) {
            return;
        }

        $row = PageSection::query()
            ->where('page', $this->page)
            ->where('section_key', $this->sectionKey)
            ->where('locale', $this->locale)
            ->first();

        if ($row === null || $row->previous_content === null) {
            return;
        }

        $row->content = $row->previous_content;
        $row->previous_content = null;
        $row->updated_by_user_id = auth()->id();
        $row->updated_at = now();
        $row->save();

        app(PageSectionService::class)->flushCache($this->page, $this->locale);

        $this->content = (string) $row->content;
        $this->open = false;

        $this->dispatch('page-section-updated', [
            'sectionKey' => $this->sectionKey,
            'html' => app(HtmlSanitizerService::class)->sanitize($this->content),
        ]);
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
        return view('livewire.page-section-editor');
    }
}
