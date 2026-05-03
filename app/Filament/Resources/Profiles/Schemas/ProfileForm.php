<?php

namespace App\Filament\Resources\Profiles\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProfileForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('workspace_id')
                    ->relationship('workspace', 'name')
                    ->required(),
                Select::make('theme_id')
                    ->relationship('theme', 'name'),
                TextInput::make('slug')
                    ->required(),
                TextInput::make('display_name')
                    ->required(),
                Textarea::make('bio')
                    ->columnSpanFull(),
                TextInput::make('avatar_path'),
                Textarea::make('theme_variables')
                    ->columnSpanFull(),
                Toggle::make('is_published')
                    ->required(),
                DateTimePicker::make('published_at'),
            ]);
    }
}
