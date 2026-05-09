<?php

use App\Filament\Pages\StripeSettingsPage;
use App\Models\Setting;
use App\Models\User;
use App\Services\SettingsService;
use Livewire\Livewire;

test('non-admin cannot access stripe settings', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($user)
        ->get('/admin/stripe-settings')
        ->assertForbidden();
});

test('admin can save stripe secret and price id', function () {
    config(['cashier.secret' => 'env_sk']);
    config(['creator.stripe_prices' => [
        'free' => null,
        'starter' => null,
        'pro' => null,
    ]]);

    $admin = User::factory()->create(['is_admin' => true]);

    Livewire::actingAs($admin)
        ->test(StripeSettingsPage::class)
        ->set('data.stripe_public', 'pk_test_123')
        ->set('data.stripe_secret', 'sk_test_abc')
        ->set('data.price_starter', 'price_1ABC')
        ->call('save')
        ->assertHasNoErrors();

    app(SettingsService::class)->flushCache();
    app(SettingsService::class)->applyRuntimeConfigOverrides();

    expect(config('cashier.key'))->toBe('pk_test_123')
        ->and(config('cashier.secret'))->toBe('sk_test_abc')
        ->and(config('creator.stripe_prices.starter'))->toBe('price_1ABC');

    expect(Setting::query()->where('key', 'cashier.secret')->exists())->toBeTrue();
});

test('invalid price id fails validation', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    Livewire::actingAs($admin)
        ->test(StripeSettingsPage::class)
        ->set('data.price_starter', 'invalid')
        ->call('save')
        ->assertHasErrors(['data.price_starter']);
});
