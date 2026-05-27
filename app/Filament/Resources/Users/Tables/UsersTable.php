<?php

namespace App\Filament\Resources\Users\Tables;

use App\Support\WorkspacePlans;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),
                TextColumn::make('workspaces.plan')
                    ->label(__('admin_settings.workspace.field_plan'))
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => $state !== null && $state !== ''
                        ? WorkspacePlans::label($state)
                        : '—')
                    ->color(fn (?string $state): ?string => $state !== null && $state !== ''
                        ? WorkspacePlans::badgeColor($state)
                        : null),
                TextColumn::make('email_verified_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('stripe_id')
                    ->searchable(),
                TextColumn::make('pm_type')
                    ->searchable(),
                TextColumn::make('pm_last_four')
                    ->searchable(),
                TextColumn::make('trial_ends_at')
                    ->dateTime()
                    ->sortable(),
                IconColumn::make('is_admin')
                    ->boolean(),
                TextColumn::make('onboarding_completed_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('plan')
                    ->label(__('admin_settings.workspace.field_plan'))
                    ->options(WorkspacePlans::options())
                    ->query(function ($query, array $data) {
                        if (blank($data['value'] ?? null)) {
                            return $query;
                        }

                        return $query->whereHas('workspaces', fn ($q) => $q->where('plan', $data['value']));
                    }),
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
