<?php

use App\Livewire\LinkManager;
use App\Models\Link;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;

test('spotify preset modal shows url input field', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(LinkManager::class)
        ->call('selectPreset', 'spotify')
        ->assertSee('Spotify-URL oder URI')
        ->assertSee('id="preset-value"', false);
});

test('spotify open url creates link with provider metadata', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(LinkManager::class)
        ->call('selectPreset', 'spotify')
        ->set('presetValue', 'https://open.spotify.com/track/6rqhFgbbKwnb9MLmUQDhG6')
        ->call('addPresetLink')
        ->assertHasNoErrors()
        ->assertDispatched('close-modal', 'add-link');

    $profile = $user->currentWorkspace()?->profile;
    $link = Link::query()->where('profile_id', $profile->id)->first();

    expect($link)->not->toBeNull()
        ->and($link->title)->toBe('Spotify')
        ->and($link->url)->toBe('https://open.spotify.com/track/6rqhFgbbKwnb9MLmUQDhG6')
        ->and($link->preset_key)->toBe('spotify')
        ->and($link->provider)->toBe('spotify')
        ->and($link->provider_id)->toBe('6rqhFgbbKwnb9MLmUQDhG6')
        ->and($link->provider_resource_type)->toBe('track')
        ->and($link->is_dynamic)->toBeFalse()
        ->and($link->opens_in_new_tab)->toBeFalse();
});

test('spotify uri creates link with provider metadata', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(LinkManager::class)
        ->call('selectPreset', 'spotify')
        ->set('presetValue', 'spotify:playlist:37i9dQZF1DXcBWIGoYBM5M')
        ->call('addPresetLink')
        ->assertHasNoErrors();

    $link = Link::query()->where('profile_id', $user->currentWorkspace()->profile->id)->first();

    expect($link->provider_resource_type)->toBe('playlist')
        ->and($link->provider_id)->toBe('37i9dQZF1DXcBWIGoYBM5M');
});

test('invalid spotify input is rejected', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(LinkManager::class)
        ->call('selectPreset', 'spotify')
        ->set('presetValue', 'not-a-spotify-link')
        ->call('addPresetLink')
        ->assertHasErrors(['presetValue']);

    expect(Link::query()->count())->toBe(0);
});

test('static spotify link renders embed on public profile', function () {
    $user = User::factory()->create();
    $profile = $user->currentWorkspace()?->profile;
    expect($profile)->not->toBeNull();

    $profile->update([
        'slug' => 'spotify-'.uniqid(),
        'is_published' => true,
    ]);

    Link::query()->create([
        'profile_id' => $profile->id,
        'link_type' => 'link',
        'title' => 'Mein Track',
        'url' => 'https://open.spotify.com/track/6rqhFgbbKwnb9MLmUQDhG6',
        'preset_key' => 'spotify',
        'provider' => 'spotify',
        'provider_id' => '6rqhFgbbKwnb9MLmUQDhG6',
        'provider_resource_type' => 'track',
        'is_dynamic' => false,
        'position' => 1,
        'is_active' => true,
        'opens_in_new_tab' => false,
        'tracking_enabled' => false,
        'show_icon' => false,
    ]);

    Cache::forget(Profile::publicProfileCacheKey($profile->slug));

    $this->get(route('public.profile', $profile->slug))
        ->assertOk()
        ->assertSee('open.spotify.com/embed/track/6rqhFgbbKwnb9MLmUQDhG6', false)
        ->assertSee('Auf Spotify abspielen', false);
});
