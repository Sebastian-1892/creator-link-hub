<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Support\WorkspacePlans;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $workspace = $this->record->currentWorkspace();
        $data['workspace_plan'] = $workspace?->plan ?? 'free';

        return $data;
    }

    protected function afterSave(): void
    {
        $plan = $this->form->getState()['workspace_plan'] ?? null;

        if (! is_string($plan) || ! WorkspacePlans::isValid($plan)) {
            return;
        }

        $workspace = $this->record->currentWorkspace();

        if ($workspace && $workspace->plan !== $plan) {
            $workspace->update(['plan' => $plan]);
        }
    }
}
