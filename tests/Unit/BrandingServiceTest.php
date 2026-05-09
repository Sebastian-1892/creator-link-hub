<?php

use App\Models\Setting;
use App\Services\BrandingService;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('text falls back to translation when no database override', function () {
    Cache::flush();
    app(SettingsService::class)->flushCache();
    app(BrandingService::class)->flushPayloadCache();

    $svc = app(BrandingService::class);
    expect($svc->text('marketing.cta_primary'))->toBe(__('branding.marketing.cta_primary'));
});

test('payload cache is invalidated after flushPayloadCache', function () {
    Cache::flush();
    app(SettingsService::class)->flushCache();

    $svc = app(BrandingService::class);
    $svc->flushPayloadCache();
    $before = $svc->payload()['brand_name'];

    Setting::query()->updateOrInsert(
        ['key' => 'branding.brand_name'],
        ['value' => 'RenamedBrand', 'is_encrypted' => false, 'updated_at' => now()]
    );
    app(SettingsService::class)->flushCache();

    expect($svc->payload()['brand_name'])->toBe($before);

    $svc->flushPayloadCache();
    expect($svc->payload()['brand_name'])->toBe('RenamedBrand');
});
