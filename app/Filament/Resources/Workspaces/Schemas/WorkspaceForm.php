<?php

namespace App\Filament\Resources\Workspaces\Schemas;

use App\Support\WorkspacePlans;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;

class WorkspaceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('name')
                    ->required(),
                Section::make(__('admin_settings.workspace.section_plan'))
                    ->schema([
                        Select::make('plan')
                            ->label(__('admin_settings.workspace.field_plan'))
                            ->options(WorkspacePlans::options())
                            ->default('free')
                            ->required()
                            ->native(false)
                            ->helperText(__('admin_settings.workspace.field_plan_help'))
                            ->rules([
                                Rule::in(WorkspacePlans::keys()),
                            ]),
                    ]),
                Toggle::make('suspended')
                    ->label(__('admin_settings.workspace.field_suspended'))
                    ->required(),
            ]);
    }
}
