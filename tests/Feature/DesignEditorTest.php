<?php

use App\Livewire\DesignEditor;
use App\Models\Link;
use App\Models\Profile;
use App\Models\Theme;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;

test('authenticated user can access design editor', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('design.edit'))
        ->assertOk()
        ->assertSee(__('Design'), false);
});

test('selecting a theme applies its colors to the form and preview', function () {
    $user = User::factory()->create();
    $theme = Theme::query()->where('slug', 'midnight-blue')->first();
    expect($theme)->not->toBeNull();

    $vars = is_array($theme->variables) ? $theme->variables : [];

    Livewire::actingAs($user)
        ->test(DesignEditor::class)
        ->set('theme_id', $theme->id)
        ->assertSet('wallpaper_color', $vars['bg'])
        ->assertSet('font_text_color', $vars['text'])
        ->assertSet('button_color', $vars['card'])
        ->assertSet('button_text_color', $vars['accent'])
        ->assertSet('wallpaper_style', 'gradient')
        ->assertSet('button_style', 'solid');
});

test('design editor header section does not nest upload forms inside livewire form', function () {
    $user = User::factory()->create();
    $profile = $user->currentWorkspace()?->profile;
    expect($profile)->not->toBeNull();
    $profile->update(['header_layout' => 'banner']);

    $html = $this->actingAs($user)
        ->get(route('design.edit', ['section' => 'header']))
        ->assertOk()
        ->getContent();

    preg_match('/<form[^>]*wire:submit="save"[^>]*>(.*?)<\/form>/is', $html, $matches);

    expect($matches[1] ?? '')->not->toContain('<form');
    expect(substr_count(strtolower($html), '<form'))->toBeGreaterThan(1);
});

test('design editor save persists settings in profile and theme variables', function () {
    $user = User::factory()->create();
    $profile = $user->currentWorkspace()?->profile;
    expect($profile)->not->toBeNull();

    Livewire::actingAs($user)
        ->test(DesignEditor::class)
        ->set('header_layout', 'hero')
        ->set('wallpaper_style', 'gradient')
        ->set('wallpaper_gradient_from', '#112233')
        ->set('wallpaper_gradient_to', '#445566')
        ->set('font_family', 'inter')
        ->set('font_text_color', '#111111')
        ->set('font_title_color', '#222222')
        ->set('button_style', 'outline')
        ->set('button_shape', 'rounded')
        ->set('button_shadow', 'hard')
        ->set('button_color', '#abcdef')
        ->set('button_text_color', '#fedcba')
        ->call('save')
        ->assertHasNoErrors();

    $profile->refresh();

    expect($profile->header_layout)->toBe('hero')
        ->and($profile->theme_variables['wallpaper']['style'])->toBe('gradient')
        ->and($profile->theme_variables['font']['family'])->toBe('inter')
        ->and($profile->theme_variables['button']['style'])->toBe('outline')
        ->and($profile->theme_variables['button']['shape'])->toBe('rounded')
        ->and($profile->theme_variables['header']['layout'])->toBe('hero');
});

test('design editor rejects invalid hex color', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(DesignEditor::class)
        ->set('button_color', 'not-a-color')
        ->call('save')
        ->assertHasErrors(['button_color']);
});

test('design editor rejects unknown font family', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(DesignEditor::class)
        ->set('font_family', 'comic-sans')
        ->call('save')
        ->assertHasErrors(['font_family']);
});

test('design editor preview reflects button shape and style in inline css', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(DesignEditor::class)
        ->set('activeSection', 'buttons')
        ->set('button_style', 'outline')
        ->set('button_shape', 'square')
        ->set('button_shadow', 'none')
        ->assertSee('background: transparent', false)
        ->assertSee('border-radius: 6px', false)
        ->assertSee('box-shadow: none', false);
});

test('design editor save auto-corrects wallpaper image style without file and persists buttons', function () {
    $user = User::factory()->create();
    $profile = $user->currentWorkspace()?->profile;
    expect($profile)->not->toBeNull();

    $profile->update([
        'theme_variables' => [
            'wallpaper' => ['style' => 'image', 'color' => '#ffffff'],
            'button' => [
                'style' => 'solid',
                'shape' => 'pill',
                'shadow' => 'soft',
                'color' => '#ffffff',
                'text_color' => '#000000',
            ],
        ],
        'wallpaper_image_path' => null,
    ]);

    Livewire::actingAs($user)
        ->test(DesignEditor::class)
        ->set('activeSection', 'buttons')
        ->set('button_style', 'outline')
        ->call('save')
        ->assertHasNoErrors();

    $profile->refresh();
    expect($profile->theme_variables['button']['style'])->toBe('outline')
        ->and($profile->theme_variables['wallpaper']['style'])->toBe('solid');
});

test('design editor save persists button settings when wallpaper image style is auto-corrected', function () {
    $user = User::factory()->create();
    $profile = $user->currentWorkspace()?->profile;
    expect($profile)->not->toBeNull();

    $profile->update([
        'theme_variables' => [
            'wallpaper' => ['style' => 'image', 'color' => '#ffffff'],
            'button' => [
                'style' => 'solid',
                'shape' => 'pill',
                'shadow' => 'soft',
                'color' => '#ffffff',
                'text_color' => '#000000',
            ],
        ],
        'wallpaper_image_path' => null,
    ]);

    Livewire::actingAs($user)
        ->test(DesignEditor::class)
        ->set('activeSection', 'buttons')
        ->set('button_style', 'outline')
        ->set('button_shape', 'square')
        ->call('save')
        ->assertHasNoErrors();

    $profile->refresh();
    expect($profile->theme_variables['button']['style'])->toBe('outline')
        ->and($profile->theme_variables['button']['shape'])->toBe('square')
        ->and($profile->theme_variables['wallpaper']['style'])->toBe('solid');
});

test('public profile reflects saved button style without manual cache flush', function () {
    $user = User::factory()->create();
    $profile = $user->currentWorkspace()?->profile;
    expect($profile)->not->toBeNull();

    $slug = 'bio-buttons-'.uniqid();
    $profile->forceFill([
        'slug' => $slug,
        'is_published' => true,
    ])->save();

    Link::query()->create([
        'profile_id' => $profile->id,
        'title' => 'Example',
        'url' => 'https://example.com',
        'position' => 0,
        'is_active' => true,
        'opens_in_new_tab' => false,
        'tracking_enabled' => false,
    ]);

    $this->actingAs($user)->get(route('public.profile', $slug))->assertOk();

    Livewire::actingAs($user)
        ->test(DesignEditor::class)
        ->set('wallpaper_style', 'solid')
        ->set('button_style', 'outline')
        ->set('button_shape', 'square')
        ->call('save')
        ->assertHasNoErrors();

    $this->get(route('public.profile', $slug))
        ->assertOk()
        ->assertSee('background: transparent', false)
        ->assertSee('border-radius: 6px', false);
});

test('design editor dismisses save notice when switching section or editing a field', function () {
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)
        ->test(DesignEditor::class)
        ->set('wallpaper_style', 'solid')
        ->call('save')
        ->assertSet('saveNotice', fn ($notice) => is_string($notice) && $notice !== '');

    foreach (['header', 'wallpaper', 'text', 'buttons', 'theme'] as $section) {
        $component
            ->call('save')
            ->assertSet('saveNotice', fn ($notice) => is_string($notice) && $notice !== '')
            ->call('setSection', $section)
            ->assertSet('saveNotice', null);
    }

    $component
        ->call('save')
        ->set('activeSection', 'wallpaper')
        ->assertSet('saveNotice', null)
        ->call('save')
        ->set('theme_filter', 'dark')
        ->assertSet('saveNotice', null);
});

test('public profile reflects saved outline button style', function () {
    Cache::flush();

    $user = User::factory()->create();
    $profile = $user->currentWorkspace()?->profile;
    expect($profile)->not->toBeNull();

    Livewire::actingAs($user)
        ->test(DesignEditor::class)
        ->set('wallpaper_style', 'solid')
        ->set('button_style', 'outline')
        ->call('save')
        ->assertHasNoErrors();

    $profile->forceFill([
        'slug' => 'design-outline-'.uniqid(),
        'is_published' => true,
    ])->save();

    Link::query()->create([
        'profile_id' => $profile->id,
        'title' => 'Example',
        'url' => 'https://example.com',
        'position' => 0,
        'is_active' => true,
        'opens_in_new_tab' => false,
        'tracking_enabled' => false,
    ]);

    Cache::forget(Profile::publicProfileCacheKey($profile->slug));

    $this->get(route('public.profile', $profile->slug))
        ->assertOk()
        ->assertSee('clh-link--style-outline', false)
        ->assertSee('background: transparent', false);
});
