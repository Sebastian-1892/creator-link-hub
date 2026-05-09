<?php

use App\Models\Setting;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('set with null or empty string removes key', function () {
    $s = app(SettingsService::class);
    $s->set('mail.default', 'smtp', false);
    expect(Setting::query()->where('key', 'mail.default')->exists())->toBeTrue();

    $s->set('mail.default', null);
    expect(Setting::query()->where('key', 'mail.default')->exists())->toBeFalse();
});

test('whitelisted keys are stored encrypted', function () {
    $s = app(SettingsService::class);
    $s->set('cashier.secret', 'sk_test_123', true);

    $row = Setting::query()->where('key', 'cashier.secret')->first();
    expect($row->is_encrypted)->toBeTrue();
    expect($row->value)->not->toBe('sk_test_123');
    expect($s->getStored('cashier.secret'))->toBe('sk_test_123');
});

test('non whitelisted key is not auto encrypted when explicit false', function () {
    $s = app(SettingsService::class);
    $s->set('mail.default', 'log', false);
    $row = Setting::query()->where('key', 'mail.default')->first();
    expect($row->is_encrypted)->toBeFalse();
    expect($row->value)->toBe('log');
});

test('getStored returns decrypted value for encrypted row', function () {
    $raw = Crypt::encryptString('secret-value');
    Setting::query()->updateOrInsert(
        ['key' => 'cashier.webhook.secret'],
        ['value' => $raw, 'is_encrypted' => true, 'updated_at' => now()]
    );
    $s = app(SettingsService::class);
    $s->flushCache();
    expect($s->getStored('cashier.webhook.secret'))->toBe('secret-value');
});
