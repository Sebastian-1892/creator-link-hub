<?php

use App\Models\Profile;
use App\Models\User;
use App\Support\ProfileDesignSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('clh public theme emits css modifier classes for button settings', function () {
    $user = User::factory()->create();
    $profile = $user->currentWorkspace()?->profile;
    expect($profile)->not->toBeNull();

    $profile->theme_variables = (new ProfileDesignSettings(
        buttonStyle: 'outline',
        buttonShape: 'square',
        buttonShadow: 'none',
    ))->toThemeVariables();

    $clh = clh_public_theme($profile);

    expect($clh['link_class'])
        ->toContain('clh-link--style-outline')
        ->toContain('clh-link--shape-square')
        ->toContain('clh-link--shadow-none')
        ->and($clh['link_style'])->toBe('');
});

test('clh public theme maps pill shape and glass style', function () {
    $user = User::factory()->create();
    $profile = $user->currentWorkspace()?->profile;
    expect($profile)->not->toBeNull();

    $profile->theme_variables = (new ProfileDesignSettings(
        buttonStyle: 'glass',
        buttonShape: 'pill',
        buttonShadow: 'strong',
    ))->toThemeVariables();

    $clh = clh_public_theme($profile);

    expect($clh['link_class'])
        ->toContain('clh-link--style-glass')
        ->toContain('clh-link--shape-pill')
        ->toContain('clh-link--shadow-strong');
});
