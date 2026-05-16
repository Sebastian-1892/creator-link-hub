<?php

use App\Filament\Pages\MailSettingsPage;
use App\Models\Setting;
use App\Models\User;
use App\Services\SettingsService;
use Livewire\Livewire;

test('guest is redirected to login for mail settings', function () {
    $this->get('/admin/mail-settings')
        ->assertRedirect();
});

test('non-admin cannot access mail settings', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($user)
        ->get('/admin/mail-settings')
        ->assertForbidden();
});

test('admin can open mail settings page', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)
        ->get('/admin/mail-settings')
        ->assertOk();
});

test('cloud deployment saves custom smtp and applies runtime config', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    Livewire::actingAs($admin)
        ->test(MailSettingsPage::class)
        ->set('data.mail_cloud_transport', 'custom_smtp')
        ->set('data.smtp_host', 'smtp.acme.test')
        ->set('data.smtp_port', '587')
        ->set('data.smtp_scheme', 'smtp')
        ->set('data.smtp_username', 'relay-user')
        ->set('data.from_address', 'from@acme.test')
        ->set('data.from_name', 'Acme')
        ->call('save')
        ->assertHasNoErrors();

    expect(Setting::query()->where('key', SettingsService::MAIL_CLOUD_TRANSPORT_MODE_KEY)->value('value'))->toBe('custom_smtp')
        ->and(Setting::query()->where('key', 'mail.mailers.smtp.host')->value('value'))->toBe('smtp.acme.test');

    app(SettingsService::class)->flushCache();
    app(SettingsService::class)->applyRuntimeConfigOverrides();

    expect(config('mail.default'))->toBe('smtp')
        ->and(config('mail.mailers.smtp.host'))->toBe('smtp.acme.test');
});

test('cloud provider mode removes tenant smtp overrides and uses sendmail', function () {
    Setting::query()->updateOrInsert(
        ['key' => 'mail.mailers.smtp.host'],
        ['value' => 'old.example.test', 'is_encrypted' => false, 'updated_at' => now()],
    );
    Setting::query()->updateOrInsert(
        ['key' => SettingsService::MAIL_CLOUD_TRANSPORT_MODE_KEY],
        ['value' => 'custom_smtp', 'is_encrypted' => false, 'updated_at' => now()],
    );

    $admin = User::factory()->create(['is_admin' => true]);

    Livewire::actingAs($admin)
        ->test(MailSettingsPage::class)
        ->set('data.mail_cloud_transport', 'provider')
        ->call('save')
        ->assertHasNoErrors();

    expect(Setting::query()->where('key', 'mail.mailers.smtp.host')->exists())->toBeFalse()
        ->and(Setting::query()->where('key', SettingsService::MAIL_CLOUD_TRANSPORT_MODE_KEY)->value('value'))->toBe('provider');

    app(SettingsService::class)->flushCache();
    app(SettingsService::class)->applyRuntimeConfigOverrides();

    expect(config('mail.default'))->toBe('sendmail');
});
