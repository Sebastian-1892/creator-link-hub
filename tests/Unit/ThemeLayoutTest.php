<?php

use App\Models\Theme;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('seeded layout template exposes layout columns', function () {
    $theme = Theme::query()->where('slug', 'modern-glass')->first();

    expect($theme)->not->toBeNull();
    expect($theme->button_style)->toBe('glass');
    expect($theme->background_style)->toBe('radial-glow');
    expect($theme->font_family)->toBe('inter');
    expect($theme->card_style)->toBe('glass');
    expect($theme->template_group)->toBe('dark');
});

test('theme variables remain cast to array', function () {
    $theme = Theme::query()->first();
    expect($theme)->not->toBeNull();
    expect($theme->variables)->toBeArray();
});
