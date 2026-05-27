<?php

use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Workspaces\Pages\EditWorkspace;
use App\Models\User;
use App\Services\WorkspaceProvisioner;
use Livewire\Livewire;

test('non-admin cannot access workspace edit', function () {
    $user = User::factory()->create(['is_admin' => false]);
    $customer = User::factory()->create();
    app(WorkspaceProvisioner::class)->provisionForUser($customer);
    $workspace = $customer->currentWorkspace();
    expect($workspace)->not->toBeNull();

    $this->actingAs($user)
        ->get("/admin/workspaces/{$workspace->id}/edit")
        ->assertForbidden();
});

test('admin can assign starter plan on workspace', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $customer = User::factory()->create();
    app(WorkspaceProvisioner::class)->provisionForUser($customer);
    $workspace = $customer->currentWorkspace();
    expect($workspace)->not->toBeNull()
        ->and($workspace->plan)->toBe('free');

    Livewire::actingAs($admin)
        ->test(EditWorkspace::class, ['record' => $workspace->id])
        ->fillForm([
            'plan' => 'starter',
        ])
        ->call('save')
        ->assertHasNoErrors();

    expect($workspace->fresh()->plan)->toBe('starter');
});

test('admin can assign pro plan on user edit', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $customer = User::factory()->create();
    app(WorkspaceProvisioner::class)->provisionForUser($customer);
    $workspace = $customer->currentWorkspace();
    expect($workspace)->not->toBeNull();

    Livewire::actingAs($admin)
        ->test(EditUser::class, ['record' => $customer->id])
        ->fillForm([
            'workspace_plan' => 'pro',
        ])
        ->call('save')
        ->assertHasNoErrors();

    expect($workspace->fresh()->plan)->toBe('pro');
});
