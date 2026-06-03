<?php

use App\Livewire\PageSectionEditor;
use App\Models\PageSection;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Livewire\Livewire;

beforeEach(function () {
    Artisan::call('page-sections:install', ['--page' => 'home', '--force' => true]);
});

test('admin edit mode shows section edit buttons on home', function () {
    Config::set('creator.i18n_inline_editor', true);

    $admin = User::factory()->create(['is_admin' => true]);

    $this->actingAs($admin)
        ->withSession(['clh_inline_edit' => true])
        ->get(route('home', ['edit' => 1]))
        ->assertOk()
        ->assertSee(__('Section bearbeiten'), false)
        ->assertSee('data-section-key="hero"', false);
});

test('guest does not see section edit controls', function () {
    Config::set('creator.i18n_inline_editor', true);

    $this->get(route('home'))
        ->assertOk()
        ->assertDontSee(__('Section bearbeiten'), false);
});

test('admin can save page section content via livewire', function () {
    Config::set('creator.i18n_inline_editor', true);

    $admin = User::factory()->create(['is_admin' => true]);

    $newHtml = '<section class="max-w-4xl mx-auto px-4 py-10 text-center"><h1 class="text-4xl font-bold">Neue Headline</h1></section>';

    Livewire::actingAs($admin)
        ->test(PageSectionEditor::class)
        ->call('openEditor', 'home', 'hero', 'de', 'Hero')
        ->call('save', $newHtml)
        ->assertHasNoErrors()
        ->assertSet('open', false);

    $row = PageSection::query()
        ->where('page', 'home')
        ->where('section_key', 'hero')
        ->where('locale', 'de')
        ->first();

    expect($row)->not->toBeNull()
        ->and($row->content)->toContain('Neue Headline')
        ->and($row->updated_by_user_id)->toBe($admin->id);
});

test('saved hero section appears on public home page', function () {
    Config::set('creator.i18n_inline_editor', true);

    app()->setLocale('de');

    PageSection::query()->updateOrCreate(
        ['page' => 'home', 'section_key' => 'hero', 'locale' => 'de'],
        [
            'content' => '<section class="max-w-4xl mx-auto px-4 py-10 text-center"><h1 class="text-4xl font-bold">Öffentliche Headline</h1></section>',
            'sort_order' => 10,
            'is_visible' => true,
        ]
    );

    app(\App\Services\PageSectionService::class)->flushCache('home', 'de');

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Öffentliche Headline', false);
});
