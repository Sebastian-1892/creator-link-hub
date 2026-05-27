<?php

use App\Models\User;
use App\Services\BrandingService;
use Illuminate\Support\Facades\App;

test('hub locale can be switched via route and persists on user', function () {
    $user = User::factory()->create(['hub_locale' => null]);

    $response = $this->actingAs($user)
        ->withSession(['_token' => 'test'])
        ->get(route('hub.locale', ['locale' => 'en']));

    $response->assertRedirect();

    expect($user->fresh()->hub_locale)->toBe('en');
    expect(session('hub_locale'))->toBe('en');
});

test('set hub locale middleware uses user preference', function () {
    $user = User::factory()->create(['hub_locale' => 'en']);

    $this->actingAs($user)->get(route('dashboard'));

    expect(App::getLocale())->toBe('en');
});

test('invalid hub locale is rejected', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/set-hub-locale/fr')
        ->assertNotFound();
});

test('marketing pages stay on app default locale even with english browser and hub session', function () {
    app(BrandingService::class)->flushPayloadCache();

    $this->withSession(['hub_locale' => 'en'])
        ->withHeaders(['Accept-Language' => 'en-US,en;q=0.9'])
        ->get(route('home'))
        ->assertOk()
        ->assertSee('Ein Link. Alle Kanäle', false)
        ->assertDontSee('One link. Every channel', false);
});
