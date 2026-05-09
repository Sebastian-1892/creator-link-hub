<?php

use App\Filament\Pages\BrandingSettingsPage;
use App\Models\Setting;
use App\Models\User;
use App\Services\BrandingService;
use App\Services\SettingsService;
use Livewire\Livewire;

test('non-admin cannot access branding settings', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($user)
        ->get('/admin/branding-settings')
        ->assertForbidden();
});

test('admin can save marketing headline', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    Livewire::actingAs($admin)
        ->test(BrandingSettingsPage::class)
        ->set('data.marketing_headline', 'Unique Headline XYZ 123')
        ->call('save')
        ->assertHasNoErrors();

    app(SettingsService::class)->flushCache();
    app(BrandingService::class)->flushPayloadCache();

    expect(Setting::query()->where('key', 'branding.marketing.headline')->value('value'))->toBe('Unique Headline XYZ 123');
    expect(brand('marketing.headline'))->toBe('Unique Headline XYZ 123');
});
