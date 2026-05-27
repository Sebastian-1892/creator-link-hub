<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Support\WorkspacePlans;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                DateTimePicker::make('email_verified_at'),
                TextInput::make('password')
                    ->password()
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->required(fn (string $operation): bool => $operation === 'create'),
                Section::make(__('admin_settings.workspace.section_plan'))
                    ->schema([
                        Select::make('workspace_plan')
                            ->label(__('admin_settings.workspace.field_plan'))
                            ->options(WorkspacePlans::options())
                            ->default('free')
                            ->native(false)
                            ->helperText(__('admin_settings.workspace.field_plan_user_help'))
                            ->rules([
                                Rule::in(WorkspacePlans::keys()),
                            ])
                            ->visible(fn (?string $operation): bool => $operation !== 'create'),
                    ]),
                TextInput::make('stripe_id'),
                TextInput::make('pm_type'),
                TextInput::make('pm_last_four'),
                DateTimePicker::make('trial_ends_at'),
                Toggle::make('is_admin')
                    ->required(),
                DateTimePicker::make('onboarding_completed_at'),
            ]);
    }
}
