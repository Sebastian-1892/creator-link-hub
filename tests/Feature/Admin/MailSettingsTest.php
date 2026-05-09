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

test('admin can save smtp host and runtime config updates', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    Livewire::actingAs($admin)
        ->test(MailSettingsPage::class)
        ->set('data.mail_default', 'smtp')
        ->set('data.smtp_host', 'mail.example.test')
        ->set('data.smtp_port', '587')
        ->set('data.smtp_scheme', 'smtp')
        ->set('data.smtp_username', 'user')
        ->set('data.from_address', 'from@example.test')
        ->set('data.from_name', 'Test')
        ->call('save')
        ->assertHasNoErrors();

    $row = Setting::query()->where('key', 'mail.mailers.smtp.host')->first();
    expect($row)->not->toBeNull()
        ->and($row->value)->toBe('mail.example.test');

    app(SettingsService::class)->flushCache();
    app(SettingsService::class)->applyRuntimeConfigOverrides();

    expect(config('mail.mailers.smtp.host'))->toBe('mail.example.test')
        ->and(config('mail.default'))->toBe('smtp');
});
