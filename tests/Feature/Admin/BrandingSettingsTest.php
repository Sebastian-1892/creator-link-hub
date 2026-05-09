<?php

use App\Filament\Pages\BrandingSettingsPage;
use App\Models\Setting;
use App\Models\User;
use App\Services\BrandingService;
use App\Services\SettingsService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

test('logo upload stores path when Filament returns stored path string', function () {
    Storage::fake('public');
    $admin = User::factory()->create(['is_admin' => true]);
    $file = UploadedFile::fake()->image('logo.png', 120, 120);

    $storedPath = $file->store('branding', 'public');
    expect($storedPath)->not->toBeEmpty();

    Livewire::actingAs($admin)
        ->test(BrandingSettingsPage::class)
        ->set('data.brand_logo_upload', [$storedPath])
        ->call('save')
        ->assertHasNoErrors();

    app(SettingsService::class)->flushCache();
    app(BrandingService::class)->flushPayloadCache();

    expect(Setting::query()->where('key', 'branding.brand_logo_path')->value('value'))->toBe($storedPath);
    expect(app(BrandingService::class)->brandLogoUrl())->not->toBeNull();
});

test('faq repeater is stored as json and readable via branding payload', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $items = [
        ['question' => 'Q Custom?', 'answer' => 'A Custom.'],
    ];

    Livewire::actingAs($admin)
        ->test(BrandingSettingsPage::class)
        ->set('data.faq_items', $items)
        ->call('save')
        ->assertHasNoErrors();

    app(SettingsService::class)->flushCache();
    app(BrandingService::class)->flushPayloadCache();

    $stored = Setting::query()->where('key', 'branding.faq.items')->value('value');
    expect($stored)->toBeString();
    expect(app(BrandingService::class)->payload()['faq']['items'][0]['question'] ?? null)->toBe('Q Custom?');
});
