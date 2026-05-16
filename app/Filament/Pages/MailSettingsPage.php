<?php

namespace App\Filament\Pages;

use App\Services\SettingsService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Mail;

/**
 * @property-read Schema $form
 */
class MailSettingsPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static ?string $slug = 'mail-settings';

    protected static bool $shouldRegisterNavigation = true;

    protected static ?int $navigationSort = 50;

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
        return __('admin_settings.mail.nav_label');
    }

    public function getTitle(): string|Htmlable
    {
        return __('admin_settings.mail.title');
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

    protected function effectiveCloudTransportMode(): string
    {
        $pick = $this->data['mail_cloud_transport'] ?? null;
        if (is_string($pick) && in_array($pick, ['provider', 'sendmail', 'custom_smtp'], true)) {
            return $pick;
        }

        return app(SettingsService::class)->resolveCloudMailTransportMode();
    }

    protected function smtpFieldsLocked(): bool
    {
        return $this->effectiveCloudTransportMode() !== 'custom_smtp';
    }

    protected function fillForm(): void
    {
        $resolvedCloudMode = app(SettingsService::class)->resolveCloudMailTransportMode();

        $mask = __('admin_settings.mail.masked_placeholder');
        $dash = __('admin_settings.mail.placeholder_not_applicable');

        $smtpHost = (string) config('mail.mailers.smtp.host');
        $smtpPort = config('mail.mailers.smtp.port') !== null ? (string) config('mail.mailers.smtp.port') : '';
        $smtpScheme = config('mail.mailers.smtp.scheme') ?? '';
        $smtpUsername = (string) (config('mail.mailers.smtp.username') ?? '');

        if ($resolvedCloudMode === 'provider') {
            $smtpHost = $mask;
            $smtpPort = $mask;
            $smtpScheme = '';
            $smtpUsername = $mask;
        } elseif ($resolvedCloudMode === 'sendmail') {
            $smtpHost = $dash;
            $smtpPort = $dash;
            $smtpScheme = '';
            $smtpUsername = $dash;
        }

        $this->form->fill([
            'mail_cloud_transport' => $resolvedCloudMode,
            'smtp_host' => $smtpHost,
            'smtp_port' => $smtpPort,
            'smtp_scheme' => $smtpScheme,
            'smtp_username' => $smtpUsername,
            'smtp_password' => '',
            'from_address' => config('mail.from.address'),
            'from_name' => config('mail.from.name'),
        ]);
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema
            ->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        $settings = app(SettingsService::class);

        return $schema
            ->components([
                Section::make(__('admin_settings.mail.section_cloud_transport'))
                    ->components([
                        Select::make('mail_cloud_transport')
                            ->label(__('admin_settings.mail.field_cloud_transport'))
                            ->options([
                                'provider' => __('admin_settings.mail.cloud_transport_provider'),
                                'sendmail' => __('admin_settings.mail.cloud_transport_sendmail'),
                                'custom_smtp' => __('admin_settings.mail.cloud_transport_custom_smtp'),
                            ])
                            ->native(false)
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (?string $state, Set $set): void {
                                $mask = __('admin_settings.mail.masked_placeholder');
                                $dash = __('admin_settings.mail.placeholder_not_applicable');
                                if ($state === 'provider') {
                                    $set('smtp_host', $mask);
                                    $set('smtp_port', $mask);
                                    $set('smtp_scheme', '');
                                    $set('smtp_username', $mask);
                                    $set('smtp_password', '');

                                    return;
                                }
                                if ($state === 'sendmail') {
                                    $set('smtp_host', $dash);
                                    $set('smtp_port', $dash);
                                    $set('smtp_scheme', '');
                                    $set('smtp_username', $dash);
                                    $set('smtp_password', '');

                                    return;
                                }
                                if ($state === 'custom_smtp') {
                                    $set('smtp_host', (string) config('mail.mailers.smtp.host'));
                                    $set('smtp_port', config('mail.mailers.smtp.port') !== null ? (string) config('mail.mailers.smtp.port') : '');
                                    $set('smtp_scheme', config('mail.mailers.smtp.scheme') ?? '');
                                    $set('smtp_username', (string) (config('mail.mailers.smtp.username') ?? ''));
                                    $set('smtp_password', '');
                                }
                            })
                            ->helperText(__('admin_settings.mail.cloud_transport_help')),
                    ]),
                Section::make(__('admin_settings.mail.section_smtp'))
                    ->components([
                        TextInput::make('smtp_host')
                            ->label(__('admin_settings.mail.field_host'))
                            ->maxLength(255)
                            ->disabled(fn (): bool => $this->smtpFieldsLocked())
                            ->helperText(fn () => $this->smtpFieldHelper('mail.mailers.smtp.host')),
                        TextInput::make('smtp_port')
                            ->label(__('admin_settings.mail.field_port'))
                            ->numeric()
                            ->disabled(fn (): bool => $this->smtpFieldsLocked())
                            ->helperText(fn () => $this->smtpFieldHelper('mail.mailers.smtp.port')),
                        Select::make('smtp_scheme')
                            ->label(__('admin_settings.mail.field_scheme'))
                            ->options([
                                '' => __('admin_settings.mail.option_scheme_default'),
                                'smtp' => __('admin_settings.mail.option_scheme_smtp'),
                                'smtps' => __('admin_settings.mail.option_scheme_smtps'),
                            ])
                            ->native(false)
                            ->disabled(fn (): bool => $this->smtpFieldsLocked())
                            ->helperText(fn () => $this->smtpFieldHelper('mail.mailers.smtp.scheme')),
                        TextInput::make('smtp_username')
                            ->label(__('admin_settings.mail.field_username'))
                            ->maxLength(255)
                            ->disabled(fn (): bool => $this->smtpFieldsLocked())
                            ->helperText(fn () => $this->smtpFieldHelper('mail.mailers.smtp.username')),
                        TextInput::make('smtp_password')
                            ->label(__('admin_settings.mail.field_password'))
                            ->password()
                            ->revealable()
                            ->maxLength(255)
                            ->disabled(fn (): bool => $this->smtpFieldsLocked())
                            ->helperText(fn () => $this->smtpPasswordHelperText($settings)),
                    ])
                    ->footerActions([
                        Action::make('clearSmtpPassword')
                            ->label(__('admin_settings.mail.action_clear_password'))
                            ->color('danger')
                            ->link()
                            ->visible(fn (): bool => $this->effectiveCloudTransportMode() === 'custom_smtp')
                            ->action(function () use ($settings): void {
                                $settings->forget('mail.mailers.smtp.password');
                                $settings->flushCache();
                                $settings->applyRuntimeConfigOverrides();
                                $this->fillForm();
                                Notification::make()
                                    ->title(__('admin_settings.mail.notify_saved'))
                                    ->success()
                                    ->send();
                            }),
                    ]),
                Section::make(__('admin_settings.mail.section_from'))
                    ->components([
                        TextInput::make('from_address')
                            ->label(__('admin_settings.mail.field_from_address'))
                            ->email()
                            ->maxLength(255)
                            ->helperText(fn () => $this->nonSecretHint('mail.from.address')),
                        TextInput::make('from_name')
                            ->label(__('admin_settings.mail.field_from_name'))
                            ->maxLength(255)
                            ->helperText(fn () => $this->nonSecretHint('mail.from.name')),
                    ]),
            ]);
    }

    protected function smtpFieldHelper(string $settingKey): string
    {
        if ($this->smtpFieldsLocked()) {
            return $this->effectiveCloudTransportMode() === 'provider'
                ? __('admin_settings.mail.cloud_smtp_locked_provider')
                : __('admin_settings.mail.cloud_smtp_locked_sendmail');
        }

        return $this->nonSecretHint($settingKey);
    }

    protected function nonSecretHint(string $settingKey): string
    {
        $settings = app(SettingsService::class);
        $effective = match ($settingKey) {
            'mail.default' => (string) config('mail.default'),
            'mail.mailers.smtp.host' => (string) config('mail.mailers.smtp.host'),
            'mail.mailers.smtp.port' => (string) config('mail.mailers.smtp.port'),
            'mail.mailers.smtp.scheme' => (string) (config('mail.mailers.smtp.scheme') ?? ''),
            'mail.mailers.smtp.username' => (string) (config('mail.mailers.smtp.username') ?? ''),
            'mail.from.address' => (string) config('mail.from.address'),
            'mail.from.name' => (string) config('mail.from.name'),
            default => '',
        };

        $source = $settings->hasStored($settingKey)
            ? __('admin_settings.source.database')
            : __('admin_settings.source.env');

        return __('admin_settings.hint_line', ['value' => $effective, 'source' => $source]);
    }

    protected function smtpPasswordHelperText(SettingsService $settings): string
    {
        if ($this->smtpFieldsLocked()) {
            return $this->effectiveCloudTransportMode() === 'provider'
                ? __('admin_settings.mail.cloud_smtp_locked_provider')
                : __('admin_settings.mail.cloud_smtp_locked_sendmail');
        }

        if ($settings->hasStored('mail.mailers.smtp.password')) {
            return __('admin_settings.source.secret_stored_db');
        }

        if (filled(config('mail.mailers.smtp.password'))) {
            return __('admin_settings.source.secret_from_env');
        }

        return __('admin_settings.source.secret_empty');
    }

    public function save(): void
    {
        abort_unless(static::canAccess(), 403);

        $data = $this->form->getState();
        $settings = app(SettingsService::class);

        $mode = (string) ($data['mail_cloud_transport'] ?? 'provider');
        if (! in_array($mode, ['provider', 'sendmail', 'custom_smtp'], true)) {
            $mode = 'provider';
        }
        $settings->set(SettingsService::MAIL_CLOUD_TRANSPORT_MODE_KEY, $mode, encrypted: false);

        if ($mode !== 'custom_smtp') {
            $this->forgetTenantSmtpOverrides($settings);
        } else {
            $host = $this->nullableString($data['smtp_host'] ?? null, acceptSmtpInput: true);
            $mask = __('admin_settings.mail.masked_placeholder');
            $dash = __('admin_settings.mail.placeholder_not_applicable');
            if ($host === null || $host === '' || $host === $mask || $host === $dash) {
                Notification::make()
                    ->title(__('admin_settings.mail.notify_custom_smtp_host_required'))
                    ->danger()
                    ->send();

                return;
            }

            $settings->set('mail.mailers.smtp.host', $host);
            $settings->set('mail.mailers.smtp.port', $this->nullableString($data['smtp_port'] ?? null, acceptSmtpInput: true), encrypted: false);

            $scheme = isset($data['smtp_scheme']) ? (string) $data['smtp_scheme'] : '';
            $settings->set('mail.mailers.smtp.scheme', $scheme === '' ? null : $scheme);

            $settings->set('mail.mailers.smtp.username', $this->nullableString($data['smtp_username'] ?? null, acceptSmtpInput: true));

            if (! empty($data['smtp_password'])) {
                $settings->set('mail.mailers.smtp.password', (string) $data['smtp_password'], true);
            }

            $settings->set('mail.default', 'smtp', encrypted: false);
        }

        $settings->set('mail.from.address', $this->nullableString($data['from_address'] ?? null));
        $settings->set('mail.from.name', $this->nullableString($data['from_name'] ?? null));

        $settings->flushCache();
        $settings->applyRuntimeConfigOverrides();

        $this->fillForm();

        Notification::make()
            ->title(__('admin_settings.mail.notify_saved'))
            ->success()
            ->send();
    }

    protected function forgetTenantSmtpOverrides(SettingsService $settings): void
    {
        foreach ([
            'mail.mailers.smtp.host',
            'mail.mailers.smtp.port',
            'mail.mailers.smtp.scheme',
            'mail.mailers.smtp.username',
            'mail.mailers.smtp.password',
            'mail.default',
        ] as $key) {
            $settings->forget($key);
        }
    }

    protected function nullableString(?string $value, bool $acceptSmtpInput = false): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $trimmed = trim($value);
        if ($acceptSmtpInput) {
            $mask = __('admin_settings.mail.masked_placeholder');
            $dash = __('admin_settings.mail.placeholder_not_applicable');
            if ($trimmed === $mask || $trimmed === $dash) {
                return null;
            }
        }

        return $trimmed;
    }

    /**
     * @return array<Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('sendTestMail')
                ->label(__('admin_settings.mail.action_test'))
                ->action('sendTestMail'),
        ];
    }

    public function sendTestMail(): void
    {
        abort_unless(static::canAccess(), 403);

        try {
            Mail::raw(__('admin_settings.mail.test_body'), function ($message): void {
                $message->to((string) auth()->user()->email)
                    ->subject(__('admin_settings.mail.test_subject'));
            });

            Notification::make()
                ->title(__('admin_settings.mail.test_ok'))
                ->success()
                ->send();
        } catch (\Throwable $e) {
            report($e);

            Notification::make()
                ->title(__('admin_settings.mail.test_failed'))
                ->danger()
                ->send();
        }
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
