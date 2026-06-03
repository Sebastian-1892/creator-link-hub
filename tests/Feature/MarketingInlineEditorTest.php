<?php

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;

beforeEach(function () {
    Artisan::call('page-sections:install', ['--page' => 'home', '--force' => true]);
});

test('admin inline edit mode shows section controls on marketing home', function () {
    Config::set('creator.i18n_inline_editor', true);

    $admin = User::factory()->create(['is_admin' => true]);

    $response = $this->actingAs($admin)
        ->withSession(['clh_inline_edit' => true])
        ->get(route('home', ['edit' => 1]))
        ->assertOk()
        ->assertSee(__('Inline-Bearbeitung aktiv'), false)
        ->assertSee(__('Section bearbeiten'), false);

    expect(substr_count($response->getContent(), 'data-section-key='))->toBeGreaterThan(3);
});

test('guest does not see inline edit controls on marketing home', function () {
    Config::set('creator.i18n_inline_editor', true);

    $this->get(route('home'))
        ->assertOk()
        ->assertDontSee(__('Section bearbeiten'), false);
});
