<?php

namespace App\Livewire;

use App\Models\Profile;
use App\Models\Theme;
use App\Support\ProfileDesignSettings;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.app')]
class DesignEditor extends Component
{
    public Profile $profile;

    #[Url(as: 'section')]
    public string $activeSection = 'theme';

    public ?int $theme_id = null;

    public string $theme_filter = 'all';

    public string $header_layout = 'classic';

    public string $wallpaper_style = 'solid';

    public string $wallpaper_color = '#f8fafc';

    public string $wallpaper_gradient_from = '#f8fafc';

    public string $wallpaper_gradient_to = '#e2e8f0';

    public int $wallpaper_gradient_angle = 165;

    public string $font_family = 'figtree';

    public string $font_text_color = '#0f172a';

    public string $font_title_color = '#0f172a';

    public string $button_style = 'solid';

    public string $button_shape = 'pill';

    public string $button_shadow = 'soft';

    public string $button_color = '#ffffff';

    public string $button_text_color = '#0f172a';

    public ?string $saveNotice = null;

    public function mount(): void
    {
        $workspace = auth()->user()?->currentWorkspace();
        abort_if(! $workspace || ! $workspace->profile, 404);

        $this->profile = $workspace->profile->load('theme');
        $this->authorize('update', $this->profile);

        $settings = ProfileDesignSettings::fromProfile($this->profile);
        $this->theme_id = $settings->themeId;
        $this->header_layout = $settings->headerLayout;
        $this->wallpaper_style = $settings->wallpaperStyle;
        $this->wallpaper_color = $settings->wallpaperColor;
        $this->wallpaper_gradient_from = $settings->wallpaperGradientFrom;
        $this->wallpaper_gradient_to = $settings->wallpaperGradientTo;
        $this->wallpaper_gradient_angle = $settings->wallpaperGradientAngle;
        $this->font_family = $settings->fontFamily;
        $this->font_text_color = $settings->fontTextColor;
        $this->font_title_color = $settings->fontTitleColor;
        $this->button_style = $settings->buttonStyle;
        $this->button_shape = $settings->buttonShape;
        $this->button_shadow = $settings->buttonShadow;
        $this->button_color = $settings->buttonColor;
        $this->button_text_color = $settings->buttonTextColor;

        if (! in_array($this->activeSection, ['theme', 'header', 'wallpaper', 'text', 'buttons'], true)) {
            $this->activeSection = 'theme';
        }
    }

    public function setSection(string $section): void
    {
        if (in_array($section, ['theme', 'header', 'wallpaper', 'text', 'buttons'], true)) {
            $this->activeSection = $section;
        }
    }

    public function updatedThemeId(mixed $value): void
    {
        $this->theme_id = ($value === '' || $value === null) ? null : (int) $value;
        $this->applyThemeDefaultsToForm();
    }

    public function save(): void
    {
        $this->wallpaper_style = ProfileDesignSettings::resolveWallpaperStyle(
            $this->wallpaper_style,
            $this->profile->wallpaper_image_path
        );

        $hex = ['required', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'];

        $this->validate([
            'theme_id' => ['nullable', 'exists:themes,id'],
            'header_layout' => ['required', Rule::in(ProfileDesignSettings::HEADER_LAYOUTS)],
            'wallpaper_style' => ['required', Rule::in(ProfileDesignSettings::WALLPAPER_STYLES)],
            'wallpaper_color' => $hex,
            'wallpaper_gradient_from' => $hex,
            'wallpaper_gradient_to' => $hex,
            'wallpaper_gradient_angle' => ['required', 'integer', 'min:0', 'max:360'],
            'font_family' => ['required', Rule::in(ProfileDesignSettings::FONT_FAMILIES)],
            'font_text_color' => $hex,
            'font_title_color' => $hex,
            'button_style' => ['required', Rule::in(ProfileDesignSettings::BUTTON_STYLES)],
            'button_shape' => ['required', Rule::in(ProfileDesignSettings::BUTTON_SHAPES)],
            'button_shadow' => ['required', Rule::in(ProfileDesignSettings::BUTTON_SHADOWS)],
            'button_color' => $hex,
            'button_text_color' => $hex,
        ]);

        if ($this->wallpaper_style === 'image' && ! $this->profile->wallpaper_image_path) {
            $this->wallpaper_style = 'solid';
        }

        $settings = new ProfileDesignSettings(
            themeId: $this->theme_id,
            headerLayout: $this->header_layout,
            wallpaperStyle: $this->wallpaper_style,
            wallpaperColor: $this->wallpaper_color,
            wallpaperGradientFrom: $this->wallpaper_gradient_from,
            wallpaperGradientTo: $this->wallpaper_gradient_to,
            wallpaperGradientAngle: $this->wallpaper_gradient_angle,
            fontFamily: $this->font_family,
            fontTextColor: $this->font_text_color,
            fontTitleColor: $this->font_title_color,
            buttonStyle: $this->button_style,
            buttonShape: $this->button_shape,
            buttonShadow: $this->button_shadow,
            buttonColor: $this->button_color,
            buttonTextColor: $this->button_text_color,
        );

        $settings->applyToProfile($this->profile);

        $this->profile->update([
            'theme_id' => $this->theme_id,
            'header_layout' => $this->header_layout,
            'theme_variables' => $this->profile->theme_variables,
        ]);

        Profile::forgetPublicProfileCacheForProfileId($this->profile->id);
        $this->profile->refresh()->load('theme');

        $this->saveNotice = __('Design gespeichert — deine Bio-Seite wurde aktualisiert.');
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

        $previewProfile = $this->buildPreviewProfile();

        return view('livewire.design-editor', [
            'themes' => $query->get(),
            'previewProfile' => $previewProfile,
            'previewPresentation' => clh_public_presentation($previewProfile),
            'previewKey' => md5(json_encode([
                $this->theme_id,
                $this->header_layout,
                $this->wallpaper_style,
                $this->wallpaper_color,
                $this->wallpaper_gradient_from,
                $this->wallpaper_gradient_to,
                $this->wallpaper_gradient_angle,
                $this->font_family,
                $this->font_text_color,
                $this->font_title_color,
                $this->button_style,
                $this->button_shape,
                $this->button_shadow,
                $this->button_color,
                $this->button_text_color,
            ])),
            'publicUrl' => $this->profile->is_published
                ? route('public.profile', $this->profile->slug)
                : null,
        ]);
    }

    protected function buildPreviewProfile(): Profile
    {
        $preview = clone $this->profile;
        $preview->theme_id = $this->theme_id;
        $preview->setRelation('theme', $this->theme_id ? Theme::query()->find($this->theme_id) : null);
        $preview->header_layout = $this->header_layout;
        $preview->theme_variables = (new ProfileDesignSettings(
            themeId: $this->theme_id,
            headerLayout: $this->header_layout,
            wallpaperStyle: $this->wallpaper_style,
            wallpaperColor: $this->wallpaper_color,
            wallpaperGradientFrom: $this->wallpaper_gradient_from,
            wallpaperGradientTo: $this->wallpaper_gradient_to,
            wallpaperGradientAngle: $this->wallpaper_gradient_angle,
            fontFamily: $this->font_family,
            fontTextColor: $this->font_text_color,
            fontTitleColor: $this->font_title_color,
            buttonStyle: $this->button_style,
            buttonShape: $this->button_shape,
            buttonShadow: $this->button_shadow,
            buttonColor: $this->button_color,
            buttonTextColor: $this->button_text_color,
        ))->toThemeVariables();

        return $preview;
    }

    protected function applyThemeDefaultsToForm(): void
    {
        if ($this->theme_id) {
            $theme = Theme::query()->find($this->theme_id);
            if ($theme) {
                $this->syncFromSettings(ProfileDesignSettings::fromTheme($theme, $this->profile));

                return;
            }
        }

        $this->syncFromSettings(ProfileDesignSettings::fromProfile($this->profile->fresh()->load('theme')));
    }

    protected function syncFromSettings(ProfileDesignSettings $settings): void
    {
        $this->header_layout = $settings->headerLayout;
        $this->wallpaper_style = $settings->wallpaperStyle;
        $this->wallpaper_color = $settings->wallpaperColor;
        $this->wallpaper_gradient_from = $settings->wallpaperGradientFrom;
        $this->wallpaper_gradient_to = $settings->wallpaperGradientTo;
        $this->wallpaper_gradient_angle = $settings->wallpaperGradientAngle;
        $this->font_family = $settings->fontFamily;
        $this->font_text_color = $settings->fontTextColor;
        $this->font_title_color = $settings->fontTitleColor;
        $this->button_style = $settings->buttonStyle;
        $this->button_shape = $settings->buttonShape;
        $this->button_shadow = $settings->buttonShadow;
        $this->button_color = $settings->buttonColor;
        $this->button_text_color = $settings->buttonTextColor;
    }
}
