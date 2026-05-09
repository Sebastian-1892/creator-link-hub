<?php

namespace App\Filament\Pages;

use App\Services\SettingsService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Str;

/**
 * @property-read Schema $form
 */
class StripeSettingsPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;

    protected static ?string $slug = 'stripe-settings';

    protected static ?int $navigationSort = 51;

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
        return __('admin_settings.stripe.nav_label');
    }

    public function getTitle(): string|Htmlable
    {
        return __('admin_settings.stripe.title');
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
        $fill = [
            'stripe_public' => config('cashier.key'),
            'stripe_secret' => '',
            'stripe_webhook' => '',
        ];

        foreach (array_keys(config('creator.plans', [])) as $planKey) {
            $fill['price_'.$planKey] = config('creator.stripe_prices.'.$planKey) ?? '';
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
        $settings = app(SettingsService::class);

        $planFields = [];
        foreach (config('creator.plans', []) as $planKey => $_planRow) {
            $planKeyStr = (string) $planKey;
            $label = ucfirst($planKeyStr);
            $planFields[] = TextInput::make('price_'.$planKeyStr)
                ->label(__('admin_settings.stripe.price_label', ['name' => $label]))
                ->placeholder('price_…')
                ->maxLength(255)
                ->rules(['nullable', 'regex:/^(?:price_[a-zA-Z0-9]+)?$/'])
                ->helperText(function () use ($planKeyStr, $label): string {
                    return $this->priceHint($planKeyStr, __('admin_settings.stripe.price_helper', ['name' => $label]));
                });
        }

        return $schema
            ->components([
                Section::make(__('admin_settings.stripe.section_keys'))
                    ->components([
                        TextInput::make('stripe_public')
                            ->label(__('admin_settings.stripe.field_public'))
                            ->maxLength(255)
                            ->helperText(fn () => $this->publicKeyHint()),
                        TextInput::make('stripe_secret')
                            ->label(__('admin_settings.stripe.field_secret'))
                            ->password()
                            ->revealable()
                            ->maxLength(255)
                            ->helperText(fn () => $this->secretHint('cashier.secret', 'cashier.secret', $settings)),
                        TextInput::make('stripe_webhook')
                            ->label(__('admin_settings.stripe.field_webhook'))
                            ->password()
                            ->revealable()
                            ->maxLength(255)
                            ->helperText(fn () => $this->secretHint('cashier.webhook.secret', 'cashier.webhook.secret', $settings)),
                    ])
                    ->footerActions([
                        Action::make('clearStripeSecret')
                            ->label(__('admin_settings.stripe.action_clear_secret'))
                            ->color('danger')
                            ->link()
                            ->action(function () use ($settings): void {
                                $settings->forget('cashier.secret');
                                $settings->flushCache();
                                $settings->applyRuntimeConfigOverrides();
                                $this->fillForm();
                                Notification::make()
                                    ->title(__('admin_settings.stripe.notify_saved'))
                                    ->success()
                                    ->send();
                            }),
                        Action::make('clearWebhookSecret')
                            ->label(__('admin_settings.stripe.action_clear_webhook'))
                            ->color('danger')
                            ->link()
                            ->action(function () use ($settings): void {
                                $settings->forget('cashier.webhook.secret');
                                $settings->flushCache();
                                $settings->applyRuntimeConfigOverrides();
                                $this->fillForm();
                                Notification::make()
                                    ->title(__('admin_settings.stripe.notify_saved'))
                                    ->success()
                                    ->send();
                            }),
                    ]),
                Section::make(__('admin_settings.stripe.section_prices'))
                    ->components($planFields),
            ]);
    }

    protected function publicKeyHint(): string
    {
        $settings = app(SettingsService::class);
        $effective = (string) (config('cashier.key') ?? '');
        $display = $effective === '' ? '—' : Str::limit($effective, 24, '…');
        $source = $settings->hasStored('cashier.key')
            ? __('admin_settings.source.database')
            : __('admin_settings.source.env');

        return __('admin_settings.hint_line', ['value' => $display, 'source' => $source]);
    }

    protected function secretHint(string $settingKey, string $configKey, SettingsService $settings): string
    {
        if ($settings->hasStored($settingKey)) {
            return __('admin_settings.source.secret_stored_db');
        }

        $val = config($configKey);
        if (filled($val)) {
            return __('admin_settings.source.secret_from_env');
        }

        return __('admin_settings.source.secret_empty');
    }

    protected function priceHint(string $planKey, string $extraLine): string
    {
        $settings = app(SettingsService::class);
        $cfgKey = 'creator.stripe_prices.'.$planKey;
        $effective = (string) (config($cfgKey) ?? '');
        $display = $effective === '' ? '—' : $effective;
        $source = $settings->hasStored($cfgKey)
            ? __('admin_settings.source.database')
            : __('admin_settings.source.env');

        return $extraLine.' '.__('admin_settings.hint_line', ['value' => $display, 'source' => $source]);
    }

    public function save(): void
    {
        abort_unless(static::canAccess(), 403);

        $data = $this->form->getState();
        $settings = app(SettingsService::class);

        $settings->set('cashier.key', $this->nullableString($data['stripe_public'] ?? null));

        if (! empty($data['stripe_secret'])) {
            $settings->set('cashier.secret', (string) $data['stripe_secret'], true);
        }

        if (! empty($data['stripe_webhook'])) {
            $settings->set('cashier.webhook.secret', (string) $data['stripe_webhook'], true);
        }

        foreach (array_keys(config('creator.plans', [])) as $planKey) {
            $field = 'price_'.$planKey;
            $value = $data[$field] ?? null;
            $settings->set('creator.stripe_prices.'.$planKey, $this->nullableString(is_string($value) ? $value : null));
        }

        $settings->flushCache();
        $settings->applyRuntimeConfigOverrides();

        $this->fillForm();

        Notification::make()
            ->title(__('admin_settings.stripe.notify_saved'))
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
