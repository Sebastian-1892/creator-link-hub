<?php

use App\Livewire\BioPageEditor;
use App\Models\Profile;
use App\Models\User;
use App\Services\WorkspaceProvisioner;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;

test('public profile shows platform credit when enabled', function () {
    Cache::flush();

    $user = User::factory()->create();
    app(WorkspaceProvisioner::class)->provisionForUser($user);

    $profile = $user->currentWorkspace()?->profile;
    expect($profile)->not->toBeNull();

    $profile->forceFill([
        'slug' => 'branding-on-'.uniqid(),
        'is_published' => true,
        'show_platform_branding' => true,
    ])->save();

    Cache::forget(Profile::publicProfileCacheKey($profile->slug));

    $this->get(route('public.profile', $profile->slug))
        ->assertOk()
        ->assertSee(__('branding.bio.platform_credit'), false);
});

test('public profile hides platform credit when disabled', function () {
    Cache::flush();

    $user = User::factory()->create();
    app(WorkspaceProvisioner::class)->provisionForUser($user);

    $profile = $user->currentWorkspace()?->profile;
    expect($profile)->not->toBeNull();

    $profile->forceFill([
        'slug' => 'branding-off-'.uniqid(),
        'is_published' => true,
        'show_platform_branding' => false,
    ])->save();

    Cache::forget(Profile::publicProfileCacheKey($profile->slug));

    $this->get(route('public.profile', $profile->slug))
        ->assertOk()
        ->assertDontSee(__('branding.bio.platform_credit'), false);
});

test('paid workspace can disable platform branding via bio editor', function () {
    $user = User::factory()->create();
    app(WorkspaceProvisioner::class)->provisionForUser($user);

    $workspace = $user->currentWorkspace();
    $workspace->update(['plan' => 'starter']);

    $profile = $workspace->profile;
    expect($profile)->not->toBeNull();

    Livewire::actingAs($user)
        ->test(BioPageEditor::class)
        ->set('show_platform_branding', false)
        ->call('save')
        ->assertHasNoErrors();

    expect($profile->fresh()->show_platform_branding)->toBeFalse();
});

test('free workspace keeps platform branding enabled on save', function () {
    $user = User::factory()->create();
    app(WorkspaceProvisioner::class)->provisionForUser($user);

    $workspace = $user->currentWorkspace();
    $workspace->update(['plan' => 'free']);

    $profile = $workspace->profile;
    expect($profile)->not->toBeNull();

    Livewire::actingAs($user)
        ->test(BioPageEditor::class)
        ->set('show_platform_branding', false)
        ->call('save')
        ->assertHasNoErrors();

    expect($profile->fresh()->show_platform_branding)->toBeTrue();
});

test('bio editor shows platform branding checkbox', function () {
    $user = User::factory()->create();
    app(WorkspaceProvisioner::class)->provisionForUser($user);

    $this->actingAs($user)
        ->get(route('bio.edit'))
        ->assertOk()
        ->assertSee(__('„Built with …“-Hinweis anzeigen'), false);
});
