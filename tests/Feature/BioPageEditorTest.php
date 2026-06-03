<?php

use App\Livewire\BioPageEditor;
use App\Models\Profile;
use App\Models\User;
use App\Services\WorkspaceProvisioner;
use Illuminate\Support\Str;
use Livewire\Livewire;

test('bio editor shows character counter and maxlength', function () {
    $user = User::factory()->create();
    app(WorkspaceProvisioner::class)->provisionForUser($user);

    $max = Profile::bioMaxLength();

    $this->actingAs($user)
        ->get(route('bio.edit'))
        ->assertOk()
        ->assertSee('maxlength="'.$max.'"', false)
        ->assertSee('0 / '.$max, false);
});

test('bio editor accepts bio up to max length', function () {
    $user = User::factory()->create();
    app(WorkspaceProvisioner::class)->provisionForUser($user);

    $profile = $user->currentWorkspace()?->profile;
    expect($profile)->not->toBeNull();

    $bio = Str::repeat('a', Profile::bioMaxLength());

    Livewire::actingAs($user)
        ->test(BioPageEditor::class)
        ->set('bio', $bio)
        ->call('save')
        ->assertHasNoErrors();

    expect($profile->fresh()->bio)->toBe($bio);
});

test('bio editor rejects bio longer than max length', function () {
    $user = User::factory()->create();
    app(WorkspaceProvisioner::class)->provisionForUser($user);

    $profile = $user->currentWorkspace()?->profile;
    expect($profile)->not->toBeNull();

    $tooLong = Str::repeat('b', Profile::bioMaxLength() + 1);

    Livewire::actingAs($user)
        ->test(BioPageEditor::class)
        ->set('bio', $tooLong)
        ->call('save')
        ->assertHasErrors(['bio']);

    expect($profile->fresh()->bio)->not->toBe($tooLong);
});

test('bio editor loads stored bio at max length on mount', function () {
    $user = User::factory()->create();
    app(WorkspaceProvisioner::class)->provisionForUser($user);

    $profile = $user->currentWorkspace()?->profile;
    expect($profile)->not->toBeNull();

    $bio = Str::repeat('z', Profile::bioMaxLength());
    $profile->update(['bio' => $bio]);

    Livewire::actingAs($user)
        ->test(BioPageEditor::class)
        ->assertSet('bio', $bio);
});
