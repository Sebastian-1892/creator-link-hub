<?php

namespace App\Filament\Pages;

use App\Services\BrandingService;
use App\Services\SettingsService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View as SchemaView;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * @property-read Schema $form
 */
class BrandingSettingsPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSwatch;

    protected static ?string $slug = 'branding-settings';

    protected static ?int $navigationSort = 48;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public static function getNavigationGroup(): string|\UnitEnum|null
    {
        return __('admin_settings.nav.group');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin_settings.branding.nav_label');
    }

    public function getTitle(): string|Htmlable
    {
        return __('admin_settings.branding.title');
    }

    public static function canAccess(): bool
    {
        return (bool) auth()->user()?->is_admin;
    }

    public function mount(): void
    {
        abort_unless(static::canAccess(), 403);

        $this->fillForm();
    }

    protected function fillForm(): void
    {
        $branding = app(BrandingService::class);
        $settings = app(SettingsService::class);

        $fill = [
            'brand_name' => $branding->brandName(),
            'brand_logo_upload' => [],
        ];

        foreach (BrandingService::colorShortKeys() as $k) {
            $stored = $settings->getStored('branding.colors.'.$k);
            $fill['color_'.$k] = (is_string($stored) && $stored !== '')
                ? $stored
                : $branding->defaultColors()[$k];
        }

        $simpleTextMap = [
            'marketing_eyebrow' => 'marketing.eyebrow',
            'marketing_headline' => 'marketing.headline',
            'marketing_subline' => 'marketing.subline',
            'marketing_cta_primary' => 'marketing.cta_primary',
            'marketing_cta_secondary' => 'marketing.cta_secondary',
            'marketing_footer_tagline' => 'marketing.footer_tagline',
            'marketing_trust_strip' => 'marketing.trust_strip',
            'marketing_final_cta_title' => 'marketing.final_cta_title',
            'marketing_final_cta_subline' => 'marketing.final_cta_subline',
            'marketing_final_cta_button' => 'marketing.final_cta_button',
            'bio_cta_label_default' => 'bio.cta_label_default',
            'bio_platform_credit' => 'bio.platform_credit',
            'bio_platform_url_label' => 'bio.platform_url_label',
        ];

        foreach ($simpleTextMap as $field => $suffix) {
            $stored = $settings->getStored('branding.'.$suffix);
            $fill[$field] = (is_string($stored) && $stored !== '')
                ? $stored
                : (string) __('branding.'.$suffix);
        }

        foreach ([1, 2, 3] as $i) {
            foreach (['title', 'text'] as $part) {
                $suffix = "marketing.steps.{$i}.{$part}";
                $field = "marketing_step_{$i}_{$part}";
                $stored = $settings->getStored('branding.'.$suffix);
                $fill[$field] = (is_string($stored) && $stored !== '')
                    ? $stored
                    : (string) __('branding.'.$suffix);
            }
        }

        foreach ([1, 2, 3] as $i) {
            foreach (['title', 'text'] as $part) {
                $suffix = "marketing.features.{$i}.{$part}";
                $field = "marketing_feature_{$i}_{$part}";
                $stored = $settings->getStored('branding.'.$suffix);
                $fill[$field] = (is_string($stored) && $stored !== '')
                    ? $stored
                    : (string) __('branding.'.$suffix);
            }
        }

        $this->form->fill($fill);
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema
            ->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        $colorPickers = [];
        foreach ($this->colorFieldDefinitions() as $suffix => $labelKey) {
            $colorPickers[] = ColorPicker::make('color_'.$suffix)
                ->label(__($labelKey))
                ->live();
        }

        $stepFields = [];
        foreach ([1, 2, 3] as $i) {
            $stepFields[] = TextInput::make("marketing_step_{$i}_title")
                ->label(__('admin_settings.branding.step_title', ['n' => $i]))
                ->maxLength(255)
                ->live();
            $stepFields[] = Textarea::make("marketing_step_{$i}_text")
                ->label(__('admin_settings.branding.step_text', ['n' => $i]))
                ->rows(2)
                ->live()
                ->columnSpanFull();
        }

        $featureFields = [];
        foreach ([1, 2, 3] as $i) {
            $featureFields[] = TextInput::make("marketing_feature_{$i}_title")
                ->label(__('admin_settings.branding.feature_title', ['n' => $i]))
                ->maxLength(255)
                ->live();
            $featureFields[] = Textarea::make("marketing_feature_{$i}_text")
                ->label(__('admin_settings.branding.feature_text', ['n' => $i]))
                ->rows(2)
                ->live()
                ->columnSpanFull();
        }

        return $schema
            ->components([
                Grid::make(['default' => 1, 'xl' => 2])
                    ->schema([
                        Group::make([
                            Section::make(__('admin_settings.branding.section_brand'))
                                ->schema([
                                    TextInput::make('brand_name')
                                        ->label(__('admin_settings.branding.field_brand_name'))
                                        ->maxLength(120)
                                        ->required()
                                        ->live(),
                                    FileUpload::make('brand_logo_upload')
                                        ->label(__('admin_settings.branding.field_logo'))
                                        ->disk('public')
                                        ->directory('branding')
                                        ->image()
                                        ->maxSize(2048)
                                        ->helperText(__('admin_settings.branding.helper_logo')),
                                ])
                                ->footerActions([
                                    Action::make('clearLogo')
                                        ->label(__('admin_settings.branding.action_clear_logo'))
                                        ->color('danger')
                                        ->link()
                                        ->action(function (): void {
                                            app(SettingsService::class)->forget('branding.brand_logo_path');
                                            app(SettingsService::class)->flushCache();
                                            app(BrandingService::class)->flushPayloadCache();
                                            $this->fillForm();
                                            Notification::make()
                                                ->title(__('admin_settings.branding.notify_saved'))
                                                ->success()
                                                ->send();
                                        }),
                                ]),
                            Section::make(__('admin_settings.branding.section_colors'))
                                ->schema($colorPickers)
                                ->columns(2),
                            Section::make(__('admin_settings.branding.section_marketing'))
                                ->schema(array_merge([
                                    TextInput::make('marketing_eyebrow')
                                        ->label(__('admin_settings.branding.marketing_eyebrow'))
                                        ->maxLength(255)
                                        ->live(),
                                    Textarea::make('marketing_headline')
                                        ->label(__('admin_settings.branding.marketing_headline'))
                                        ->rows(3)
                                        ->live()
                                        ->columnSpanFull(),
                                    Textarea::make('marketing_subline')
                                        ->label(__('admin_settings.branding.marketing_subline'))
                                        ->rows(3)
                                        ->live()
                                        ->columnSpanFull(),
                                    TextInput::make('marketing_cta_primary')
                                        ->label(__('admin_settings.branding.marketing_cta_primary'))
                                        ->maxLength(120)
                                        ->live(),
                                    TextInput::make('marketing_cta_secondary')
                                        ->label(__('admin_settings.branding.marketing_cta_secondary'))
                                        ->maxLength(120)
                                        ->live(),
                                    Textarea::make('marketing_footer_tagline')
                                        ->label(__('admin_settings.branding.marketing_footer_tagline'))
                                        ->rows(2)
                                        ->live()
                                        ->columnSpanFull(),
                                    TextInput::make('marketing_trust_strip')
                                        ->label(__('admin_settings.branding.marketing_trust_strip'))
                                        ->maxLength(255)
                                        ->live(),
                                    TextInput::make('marketing_final_cta_title')
                                        ->label(__('admin_settings.branding.marketing_final_cta_title'))
                                        ->maxLength(255)
                                        ->live(),
                                    Textarea::make('marketing_final_cta_subline')
                                        ->label(__('admin_settings.branding.marketing_final_cta_subline'))
                                        ->rows(2)
                                        ->live()
                                        ->columnSpanFull(),
                                    TextInput::make('marketing_final_cta_button')
                                        ->label(__('admin_settings.branding.marketing_final_cta_button'))
                                        ->maxLength(120)
                                        ->live(),
                                ], $stepFields, $featureFields))
                                ->columns(2),
                            Section::make(__('admin_settings.branding.section_bio'))
                                ->schema([
                                    TextInput::make('bio_cta_label_default')
                                        ->label(__('admin_settings.branding.bio_cta_default'))
                                        ->maxLength(120)
                                        ->live(),
                                    TextInput::make('bio_platform_credit')
                                        ->label(__('admin_settings.branding.bio_platform_credit'))
                                        ->maxLength(255)
                                        ->live(),
                                    TextInput::make('bio_platform_url_label')
                                        ->label(__('admin_settings.branding.bio_platform_url_label'))
                                        ->maxLength(120)
                                        ->live(),
                                ])
                                ->columns(1),
                        ])
                            ->columnSpan(['default' => 1, 'xl' => 1]),
                        Section::make(__('admin_settings.branding.preview'))
                            ->schema([
                                SchemaView::make('filament.schemas.components.branding-preview')
                                    ->viewData(fn (): array => [
                                        'p' => $this->data ?? [],
                                    ]),
                            ])
                            ->columnSpan(['default' => 1, 'xl' => 1]),
                    ]),
            ]);
    }

    /**
     * @return array<string, string>
     */
    protected function colorFieldDefinitions(): array
    {
        return [
            'primary' => 'admin_settings.branding.color_primary',
            'primary_contrast' => 'admin_settings.branding.color_primary_contrast',
            'accent' => 'admin_settings.branding.color_accent',
            'bg' => 'admin_settings.branding.color_bg',
            'bg_alt' => 'admin_settings.branding.color_bg_alt',
            'text' => 'admin_settings.branding.color_text',
            'text_muted' => 'admin_settings.branding.color_text_muted',
            'card' => 'admin_settings.branding.color_card',
            'border' => 'admin_settings.branding.color_border',
        ];
    }

    public function save(): void
    {
        abort_unless(static::canAccess(), 403);

        $data = $this->form->getState();
        $settings = app(SettingsService::class);

        $settings->set('branding.brand_name', $this->nullableString($data['brand_name'] ?? null));

        $upload = $data['brand_logo_upload'] ?? null;
        if ($upload instanceof TemporaryUploadedFile) {
            $path = $upload->store('branding', 'public');
            if (is_string($path) && $path !== '') {
                $settings->set('branding.brand_logo_path', $path);
            }
        } elseif (is_array($upload)) {
            $first = $upload[0] ?? null;
            if ($first instanceof TemporaryUploadedFile) {
                $path = $first->store('branding', 'public');
                if (is_string($path) && $path !== '') {
                    $settings->set('branding.brand_logo_path', $path);
                }
            }
        }

        foreach (BrandingService::colorShortKeys() as $k) {
            $field = 'color_'.$k;
            $raw = $data[$field] ?? null;
            $settings->set('branding.colors.'.$k, $this->nullableString(is_string($raw) ? $raw : null));
        }

        $simpleTextMap = [
            'marketing_eyebrow' => 'marketing.eyebrow',
            'marketing_headline' => 'marketing.headline',
            'marketing_subline' => 'marketing.subline',
            'marketing_cta_primary' => 'marketing.cta_primary',
            'marketing_cta_secondary' => 'marketing.cta_secondary',
            'marketing_footer_tagline' => 'marketing.footer_tagline',
            'marketing_trust_strip' => 'marketing.trust_strip',
            'marketing_final_cta_title' => 'marketing.final_cta_title',
            'marketing_final_cta_subline' => 'marketing.final_cta_subline',
            'marketing_final_cta_button' => 'marketing.final_cta_button',
            'bio_cta_label_default' => 'bio.cta_label_default',
            'bio_platform_credit' => 'bio.platform_credit',
            'bio_platform_url_label' => 'bio.platform_url_label',
        ];

        foreach ($simpleTextMap as $field => $suffix) {
            $settings->set('branding.'.$suffix, $this->nullableString($data[$field] ?? null));
        }

        foreach ([1, 2, 3] as $i) {
            foreach (['title', 'text'] as $part) {
                $suffix = "marketing.steps.{$i}.{$part}";
                $field = "marketing_step_{$i}_{$part}";
                $settings->set('branding.'.$suffix, $this->nullableString($data[$field] ?? null));
            }
        }

        foreach ([1, 2, 3] as $i) {
            foreach (['title', 'text'] as $part) {
                $suffix = "marketing.features.{$i}.{$part}";
                $field = "marketing_feature_{$i}_{$part}";
                $settings->set('branding.'.$suffix, $this->nullableString($data[$field] ?? null));
            }
        }

        $settings->flushCache();
        app(BrandingService::class)->flushPayloadCache();

        $this->fillForm();

        Notification::make()
            ->title(__('admin_settings.branding.notify_saved'))
            ->success()
            ->send();
    }

    protected function nullableString(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        return $value;
    }

    /**
     * @return array<Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('resetColors')
                ->label(__('admin_settings.branding.action_reset_colors'))
                ->color('gray')
                ->action(function (): void {
                    $defaults = app(BrandingService::class)->defaultColors();
                    $data = $this->data ?? [];
                    foreach ($defaults as $k => $hex) {
                        $data['color_'.$k] = $hex;
                    }
                    $this->data = $data;
                    Notification::make()
                        ->title(__('admin_settings.branding.action_reset_colors'))
                        ->success()
                        ->send();
                }),
        ];
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getFormContentComponent(),
            ]);
    }

    public function getFormContentComponent(): Component
    {
        return Form::make([EmbeddedSchema::make('form')])
            ->id('form')
            ->livewireSubmitHandler('save')
            ->footer([
                Actions::make([
                    Action::make('save')
                        ->label(__('admin_settings.mail.action_save'))
                        ->submit('save')
                        ->keyBindings(['mod+s']),
                ])
                    ->alignment(Alignment::Start)
                    ->key('form-actions'),
            ]);
    }

    protected function hasFullWidthFormActions(): bool
    {
        return false;
    }
}
