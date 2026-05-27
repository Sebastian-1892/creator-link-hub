<?php

use App\Livewire\LinkManager;
use App\Models\Link;
use App\Models\User;
use Livewire\Livewire;

test('instagram preset creates link with built url', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(LinkManager::class)
        ->call('selectPreset', 'instagram')
        ->set('presetValue', 'creator')
        ->call('addPresetLink')
        ->assertHasNoErrors()
        ->assertDispatched('close-modal', 'add-link');

    $profile = $user->currentWorkspace()?->profile;
    expect($profile)->not->toBeNull();

    $link = Link::query()->where('profile_id', $profile->id)->first();
    expect($link)->not->toBeNull()
        ->and($link->title)->toBe('Instagram')
        ->and($link->url)->toBe('https://instagram.com/creator')
        ->and($link->preset_key)->toBe('instagram')
        ->and($link->show_icon)->toBeTrue();
});

test('preset link accepts custom display title', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(LinkManager::class)
        ->call('selectPreset', 'youtube')
        ->set('presetValue', 'NazaledTV')
        ->set('newTitle', 'Mein Kanal')
        ->call('addPresetLink')
        ->assertHasNoErrors();

    $link = Link::query()->where('profile_id', $user->currentWorkspace()->profile->id)->first();
    expect($link->title)->toBe('Mein Kanal')
        ->and($link->preset_key)->toBe('youtube');
});

test('close add modal resets preset selection', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(LinkManager::class)
        ->call('selectPreset', 'instagram')
        ->call('closeAddModal')
        ->assertSet('presetKey', null)
        ->assertDispatched('close-modal', 'add-link');
});

test('invalid username is rejected', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(LinkManager::class)
        ->call('selectPreset', 'instagram')
        ->set('presetValue', 'bad name!')
        ->call('addPresetLink')
        ->assertHasErrors(['presetValue']);

    $profile = $user->currentWorkspace()?->profile;
    expect(Link::query()->where('profile_id', $profile->id)->count())->toBe(0);
});

test('custom preset creates link with title and url', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(LinkManager::class)
        ->call('selectPreset', 'custom')
        ->set('newTitle', 'Mein Shop')
        ->set('newUrl', 'https://example.com/shop')
        ->call('addPresetLink')
        ->assertHasNoErrors();

    $profile = $user->currentWorkspace()?->profile;
    $link = Link::query()->where('profile_id', $profile->id)->first();

    expect($link)->not->toBeNull()
        ->and($link->title)->toBe('Mein Shop')
        ->and($link->url)->toBe('https://example.com/shop');
});

test('clear preset returns to selection state', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(LinkManager::class)
        ->call('selectPreset', 'github')
        ->assertSet('presetKey', 'github')
        ->call('clearPreset')
        ->assertSet('presetKey', null);
});
