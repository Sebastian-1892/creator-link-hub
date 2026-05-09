<?php

use App\Models\Profile;
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
