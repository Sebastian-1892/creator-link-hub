<?php

use App\Models\PageSection;
use Illuminate\Support\Facades\Artisan;

beforeEach(function () {
    Artisan::call('page-sections:install', ['--page' => 'home', '--force' => true]);
});

test('hidden page section is not rendered on home', function () {
    app()->setLocale('de');

    PageSection::query()
        ->where('page', 'home')
        ->where('section_key', 'trust')
        ->where('locale', 'de')
        ->update(['is_visible' => false]);

    app(\App\Services\PageSectionService::class)->flushCache('home', 'de');

    $trustStrip = app(\App\Services\TranslationService::class)->text('marketing.trust_strip', 'de');

    $this->get(route('home'))
        ->assertOk()
        ->assertDontSee($trustStrip, false);
});
