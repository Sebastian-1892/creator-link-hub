<?php

namespace App\Filament\Resources\Workspaces\Tables;

use App\Support\WorkspacePlans;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class WorkspacesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label(__('admin_settings.workspace.column_user'))
                    ->searchable(),
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('plan')
                    ->label(__('admin_settings.workspace.field_plan'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => WorkspacePlans::label($state))
                    ->color(fn (string $state): ?string => WorkspacePlans::badgeColor($state))
                    ->sortable(),
                IconColumn::make('suspended')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('plan')
                    ->label(__('admin_settings.workspace.field_plan'))
                    ->options(WorkspacePlans::options()),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
