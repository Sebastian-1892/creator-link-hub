<?php

namespace App\Filament\Pages;

use App\Services\BrandingService;
use App\Services\SettingsService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
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

        $logoPath = $settings->getStored('branding.brand_logo_path');
        $fill = [
            'brand_name' => $branding->brandName(),
            'brand_logo_upload' => (is_string($logoPath) && $logoPath !== '') ? [$logoPath] : [],
        ];

        foreach (BrandingService::colorShortKeys() as $k) {
            $stored = $settings->getStored('branding.colors.'.$k);
            $fill['color_'.$k] = (is_string($stored) && $stored !== '')
                ? $stored
                : $branding->defaultColors()[$k];
        }

        $simpleTextMap = $this->simpleTextFieldMap();
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

        foreach ([1, 2, 3] as $i) {
            foreach (['title', 'text', 'icon'] as $part) {
                $suffix = "marketing.cards.{$i}.{$part}";
                $field = "marketing_card_{$i}_{$part}";
                $stored = $settings->getStored('branding.'.$suffix);
                $fill[$field] = (is_string($stored) && $stored !== '')
                    ? $stored
                    : (string) __('branding.'.$suffix);
            }
        }

        foreach (['impressum_html', 'datenschutz_html', 'agb_html'] as $key) {
            $suffix = 'legal.'.$key;
            $field = 'legal_'.$key;
            $stored = $settings->getStored('branding.'.$suffix);
            $fill[$field] = (is_string($stored) && $stored !== '')
                ? $stored
                : (string) __('branding.'.$suffix);
        }

        $fill['faq_items'] = $branding->list('faq.items');
        $fill['help_sections'] = $branding->list('help.sections');

        $plans = $branding->pricingPlans();
        foreach (['free', 'starter', 'pro'] as $planKey) {
            $p = $plans[$planKey] ?? [];
            $fill["pricing_{$planKey}_name"] = $p['name'] ?? '';
            $fill["pricing_{$planKey}_price"] = $p['price'] ?? '';
            $fill["pricing_{$planKey}_period"] = $p['period'] ?? '';
            $fill["pricing_{$planKey}_features"] = implode("\n", $p['features'] ?? []);
            $fill["pricing_{$planKey}_cta"] = $p['cta'] ?? '';
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

        $cardFields = [];
        foreach ([1, 2, 3] as $i) {
            $cardFields[] = TextInput::make("marketing_card_{$i}_title")
                ->label(__('admin_settings.branding.card_title', ['n' => $i]))
                ->maxLength(255)
                ->live();
            $cardFields[] = Textarea::make("marketing_card_{$i}_text")
                ->label(__('admin_settings.branding.card_text', ['n' => $i]))
                ->rows(2)
                ->live()
                ->columnSpanFull();
            $cardFields[] = TextInput::make("marketing_card_{$i}_icon")
                ->label(__('admin_settings.branding.card_icon', ['n' => $i]))
                ->maxLength(32)
                ->live();
        }

        $pricingFieldsets = [];
        foreach (['free' => __('admin_settings.branding.plan_free'), 'starter' => __('admin_settings.branding.plan_starter'), 'pro' => __('admin_settings.branding.plan_pro')] as $planKey => $planLabel) {
            $pricingFieldsets[] = Fieldset::make($planLabel)
                ->schema([
                    TextInput::make("pricing_{$planKey}_name")
                        ->label(__('admin_settings.branding.plan_name'))
                        ->maxLength(120)
                        ->live(),
                    TextInput::make("pricing_{$planKey}_price")
                        ->label(__('admin_settings.branding.plan_price'))
                        ->maxLength(64)
                        ->live(),
                    TextInput::make("pricing_{$planKey}_period")
                        ->label(__('admin_settings.branding.plan_period'))
                        ->maxLength(64)
                        ->live(),
                    Textarea::make("pricing_{$planKey}_features")
                        ->label(__('admin_settings.branding.plan_features'))
                        ->helperText(__('admin_settings.branding.plan_features_help'))
                        ->rows(5)
                        ->live()
                        ->columnSpanFull(),
                    TextInput::make("pricing_{$planKey}_cta")
                        ->label(__('admin_settings.branding.plan_cta'))
                        ->maxLength(120)
                        ->live(),
                ])
                ->columns(2);
        }

        return $schema
            ->components([
                Grid::make(['default' => 1, 'xl' => 2])
                    ->schema([
                        Tabs::make('brandingTabs')
                            ->tabs([
                                Tab::make(__('admin_settings.branding.tab_brand'))
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
                                    ->columns(1),
                                Tab::make(__('admin_settings.branding.tab_colors'))
                                    ->schema($colorPickers)
                                    ->columns(2),
                                Tab::make(__('admin_settings.branding.tab_marketing'))
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
                                        TextInput::make('marketing_trust_count')
                                            ->label(__('admin_settings.branding.marketing_trust_count'))
                                            ->maxLength(64)
                                            ->live(),
                                        TextInput::make('marketing_trust_count_label')
                                            ->label(__('admin_settings.branding.marketing_trust_count_label'))
                                            ->maxLength(255)
                                            ->live(),
                                        TextInput::make('marketing_home_templates_title')
                                            ->label(__('admin_settings.branding.marketing_home_templates_title'))
                                            ->maxLength(255)
                                            ->live(),
                                        Textarea::make('marketing_home_templates_subline')
                                            ->label(__('admin_settings.branding.marketing_home_templates_subline'))
                                            ->rows(2)
                                            ->live()
                                            ->columnSpanFull(),
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
                                        Section::make(__('admin_settings.branding.section_marketing_cards'))
                                            ->schema($cardFields)
                                            ->columns(2),
                                        Section::make(__('admin_settings.branding.section_steps'))
                                            ->schema($stepFields)
                                            ->columns(2)
                                            ->collapsed(),
                                        Section::make(__('admin_settings.branding.section_features'))
                                            ->schema($featureFields)
                                            ->columns(2)
                                            ->collapsed(),
                                    ]))
                                    ->columns(2),
                                Tab::make(__('admin_settings.branding.tab_pricing'))
                                    ->schema(array_merge([
                                        TextInput::make('pricing_title')
                                            ->label(__('admin_settings.branding.pricing_page_title'))
                                            ->maxLength(255)
                                            ->live(),
                                        Textarea::make('pricing_subline')
                                            ->label(__('admin_settings.branding.pricing_page_subline'))
                                            ->rows(2)
                                            ->live()
                                            ->columnSpanFull(),
                                    ], $pricingFieldsets))
                                    ->columns(1),
                                Tab::make(__('admin_settings.branding.tab_faq'))
                                    ->schema([
                                        TextInput::make('faq_title')
                                            ->label(__('admin_settings.branding.faq_page_title'))
                                            ->maxLength(255)
                                            ->live(),
                                        Repeater::make('faq_items')
                                            ->label(__('admin_settings.branding.faq_items'))
                                            ->schema([
                                                TextInput::make('question')
                                                    ->label(__('admin_settings.branding.faq_question'))
                                                    ->required()
                                                    ->maxLength(500)
                                                    ->columnSpanFull(),
                                                Textarea::make('answer')
                                                    ->label(__('admin_settings.branding.faq_answer'))
                                                    ->required()
                                                    ->rows(4)
                                                    ->columnSpanFull(),
                                            ])
                                            ->columns(1)
                                            ->collapsible()
                                            ->itemLabel(fn (array $state): ?string => $state['question'] ?? null),
                                    ]),
                                Tab::make(__('admin_settings.branding.tab_help'))
                                    ->schema([
                                        TextInput::make('help_title')
                                            ->label(__('admin_settings.branding.help_page_title'))
                                            ->maxLength(255)
                                            ->live(),
                                        Textarea::make('help_intro')
                                            ->label(__('admin_settings.branding.help_intro'))
                                            ->rows(3)
                                            ->live()
                                            ->columnSpanFull(),
                                        Repeater::make('help_sections')
                                            ->label(__('admin_settings.branding.help_sections'))
                                            ->schema([
                                                TextInput::make('heading')
                                                    ->label(__('admin_settings.branding.help_heading'))
                                                    ->required()
                                                    ->maxLength(255)
                                                    ->columnSpanFull(),
                                                Textarea::make('body')
                                                    ->label(__('admin_settings.branding.help_body'))
                                                    ->required()
                                                    ->rows(5)
                                                    ->columnSpanFull(),
                                            ])
                                            ->columns(1)
                                            ->collapsible()
                                            ->itemLabel(fn (array $state): ?string => $state['heading'] ?? null),
                                    ]),
                                Tab::make(__('admin_settings.branding.tab_legal'))
                                    ->schema([
                                        Textarea::make('legal_impressum_html')
                                            ->label(__('admin_settings.branding.legal_impressum'))
                                            ->rows(12)
                                            ->helperText(__('admin_settings.branding.legal_helper'))
                                            ->columnSpanFull(),
                                        Textarea::make('legal_datenschutz_html')
                                            ->label(__('admin_settings.branding.legal_datenschutz'))
                                            ->rows(12)
                                            ->helperText(__('admin_settings.branding.legal_helper'))
                                            ->columnSpanFull(),
                                        Textarea::make('legal_agb_html')
                                            ->label(__('admin_settings.branding.legal_agb'))
                                            ->rows(12)
                                            ->helperText(__('admin_settings.branding.legal_helper'))
                                            ->columnSpanFull(),
                                    ]),
                                Tab::make(__('admin_settings.branding.tab_footer'))
                                    ->schema([
                                        TextInput::make('footer_brand_label')
                                            ->label(__('admin_settings.branding.footer_brand_label'))
                                            ->maxLength(120)
                                            ->live(),
                                        TextInput::make('footer_nav_label')
                                            ->label(__('admin_settings.branding.footer_nav_label'))
                                            ->maxLength(120)
                                            ->live(),
                                        TextInput::make('footer_legal_label')
                                            ->label(__('admin_settings.branding.footer_legal_label'))
                                            ->maxLength(120)
                                            ->live(),
                                    ])
                                    ->columns(1),
                                Tab::make(__('admin_settings.branding.tab_bio'))
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
                                        Textarea::make('bio_cookie_text')
                                            ->label(__('admin_settings.branding.bio_cookie_text'))
                                            ->rows(3)
                                            ->live()
                                            ->columnSpanFull(),
                                        TextInput::make('bio_cookie_button')
                                            ->label(__('admin_settings.branding.bio_cookie_button'))
                                            ->maxLength(64)
                                            ->live(),
                                    ])
                                    ->columns(1),
                            ])
                            ->columnSpan(['default' => 1, 'xl' => 1]),
                        Group::make([
                            Section::make(__('admin_settings.branding.preview'))
                                ->schema([
                                    SchemaView::make('filament.schemas.components.branding-preview')
                                        ->viewData(fn (): array => [
                                            'p' => $this->data ?? [],
                                        ]),
                                ]),
                        ])
                            ->columnSpan(['default' => 1, 'xl' => 1]),
                    ]),
            ]);
    }

    /**
     * @return array<string, string>
     */
    protected function simpleTextFieldMap(): array
    {
        return [
            'marketing_eyebrow' => 'marketing.eyebrow',
            'marketing_headline' => 'marketing.headline',
            'marketing_subline' => 'marketing.subline',
            'marketing_cta_primary' => 'marketing.cta_primary',
            'marketing_cta_secondary' => 'marketing.cta_secondary',
            'marketing_footer_tagline' => 'marketing.footer_tagline',
            'marketing_trust_strip' => 'marketing.trust_strip',
            'marketing_trust_count' => 'marketing.trust_count',
            'marketing_trust_count_label' => 'marketing.trust_count_label',
            'marketing_home_templates_title' => 'marketing.home_templates_title',
            'marketing_home_templates_subline' => 'marketing.home_templates_subline',
            'marketing_final_cta_title' => 'marketing.final_cta_title',
            'marketing_final_cta_subline' => 'marketing.final_cta_subline',
            'marketing_final_cta_button' => 'marketing.final_cta_button',
            'pricing_title' => 'pricing.title',
            'pricing_subline' => 'pricing.subline',
            'faq_title' => 'faq.title',
            'help_title' => 'help.title',
            'help_intro' => 'help.intro',
            'footer_legal_label' => 'footer.legal_label',
            'footer_nav_label' => 'footer.nav_label',
            'footer_brand_label' => 'footer.brand_label',
            'bio_cta_label_default' => 'bio.cta_label_default',
            'bio_platform_credit' => 'bio.platform_credit',
            'bio_platform_url_label' => 'bio.platform_url_label',
            'bio_cookie_text' => 'bio.cookie_text',
            'bio_cookie_button' => 'bio.cookie_button',
        ];
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
        $path = null;
        if (is_array($upload)) {
            $first = reset($upload);
            if (is_string($first) && $first !== '') {
                $path = $first;
            } elseif ($first instanceof TemporaryUploadedFile) {
                $storedPath = $first->store('branding', 'public');
                if (is_string($storedPath) && $storedPath !== '') {
                    $path = $storedPath;
                }
            }
        } elseif (is_string($upload) && $upload !== '') {
            $path = $upload;
        } elseif ($upload instanceof TemporaryUploadedFile) {
            $storedPath = $upload->store('branding', 'public');
            if (is_string($storedPath) && $storedPath !== '') {
                $path = $storedPath;
            }
        }
        if ($path !== null) {
            $settings->set('branding.brand_logo_path', $path);
        }

        foreach (BrandingService::colorShortKeys() as $k) {
            $field = 'color_'.$k;
            $raw = $data[$field] ?? null;
            $settings->set('branding.colors.'.$k, $this->nullableString(is_string($raw) ? $raw : null));
        }

        foreach ($this->simpleTextFieldMap() as $field => $suffix) {
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

        foreach ([1, 2, 3] as $i) {
            foreach (['title', 'text', 'icon'] as $part) {
                $suffix = "marketing.cards.{$i}.{$part}";
                $field = "marketing_card_{$i}_{$part}";
                $settings->set('branding.'.$suffix, $this->nullableString($data[$field] ?? null));
            }
        }

        foreach (['impressum_html', 'datenschutz_html', 'agb_html'] as $key) {
            $field = 'legal_'.$key;
            $settings->set('branding.legal.'.$key, $this->nullableString($data[$field] ?? null));
        }

        $faqItems = $data['faq_items'] ?? [];
        $settings->set('branding.faq.items', is_array($faqItems) ? json_encode(array_values($faqItems)) : null);

        $helpSections = $data['help_sections'] ?? [];
        $settings->set('branding.help.sections', is_array($helpSections) ? json_encode(array_values($helpSections)) : null);

        $plans = [];
        foreach (['free', 'starter', 'pro'] as $planKey) {
            $featuresRaw = $data["pricing_{$planKey}_features"] ?? '';
            $features = is_string($featuresRaw)
                ? array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $featuresRaw) ?: [])))
                : [];
            $plans[$planKey] = [
                'name' => $this->nullableString($data["pricing_{$planKey}_name"] ?? null) ?? '',
                'price' => $this->nullableString($data["pricing_{$planKey}_price"] ?? null) ?? '',
                'period' => $this->nullableString($data["pricing_{$planKey}_period"] ?? null) ?? '',
                'features' => $features,
                'cta' => $this->nullableString($data["pricing_{$planKey}_cta"] ?? null) ?? '',
            ];
        }
        $settings->set('branding.pricing.plans', json_encode($plans));

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
