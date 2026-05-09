<?php

use App\Models\Link;
use App\Models\Profile;
use App\Models\Theme;
use App\Models\User;
use App\Services\WorkspaceProvisioner;
use Illuminate\Support\Facades\Cache;

test('marketing home renders default branding headline', function () {
    Cache::flush();

    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertSee(__('branding.marketing.headline'), false);
});

test('public profile renders display name and branding css variables', function () {
    Cache::flush();

    $user = User::factory()->create(['name' => 'Bio Tester']);
    app(WorkspaceProvisioner::class)->provisionForUser($user);

    $profile = $user->currentWorkspace()?->profile;
    expect($profile)->not->toBeNull();

    $profile->forceFill([
        'slug' => 'bio-test-'.uniqid(),
        'theme_id' => null,
        'theme_variables' => null,
        'is_published' => true,
        'display_name' => 'Public Bio Name',
    ])->save();

    Cache::forget(Profile::publicProfileCacheKey($profile->slug));

    $response = $this->get(route('public.profile', $profile->slug));

    $response->assertOk();
    $response->assertSee('Public Bio Name', false);
    $response->assertSee('--clh-bg:', false);
    $response->assertSee(__('branding.colors.bg'), false);
});

test('pricing page renders plan name from branding payload', function () {
    Cache::flush();
    $response = $this->get(route('pricing'));
    $response->assertOk();
    $name = branding_payload()['pricing']['plans']['free']['name'];
    expect($name)->not->toBe('');
    $response->assertSee($name, false);
});

test('faq page renders dynamic faq item question', function () {
    Cache::flush();
    $response = $this->get(route('faq'));
    $response->assertOk();
    $q = branding_payload()['faq']['items'][0]['question'] ?? '';
    expect($q)->not->toBe('');
    $response->assertSee($q, false);
});

test('legal impressum renders branding legal markdown heading', function () {
    Cache::flush();
    $response = $this->get(route('legal.impressum'));
    $response->assertOk();
    $response->assertSee('Impressum', false);
});

test('public profile with glass button theme renders backdrop blur', function () {
    Cache::flush();

    $theme = Theme::query()->where('button_style', 'glass')->first();
    expect($theme)->not->toBeNull();

    $user = User::factory()->create(['name' => 'Glass Tester']);
    app(WorkspaceProvisioner::class)->provisionForUser($user);

    $profile = $user->currentWorkspace()?->profile;
    expect($profile)->not->toBeNull();

    $profile->forceFill([
        'slug' => 'glass-bio-'.uniqid(),
        'theme_id' => $theme->id,
        'theme_variables' => null,
        'is_published' => true,
        'display_name' => 'Glass Bio',
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

    $response = $this->get(route('public.profile', $profile->slug));
    $response->assertOk();
    $response->assertSee('blur(14px)', false);
});
