<?php

use App\Models\User;
use Illuminate\Support\Facades\Config;

test('admin inline edit mode shows edit controls on marketing home texts', function () {
    Config::set('creator.i18n_inline_editor', true);

    $admin = User::factory()->create(['is_admin' => true]);

    $response = $this->actingAs($admin)
        ->withSession(['clh_inline_edit' => true])
        ->get(route('home', ['edit' => 1]))
        ->assertOk()
        ->assertSee(__('Inline-Bearbeitung aktiv'), false);

    expect(substr_count($response->getContent(), 'open-translation-editor'))->toBeGreaterThan(10);
});

test('guest does not see inline edit controls on marketing home', function () {
    Config::set('creator.i18n_inline_editor', true);

    $this->get(route('home'))
        ->assertOk()
        ->assertDontSee('✎', false);
});
