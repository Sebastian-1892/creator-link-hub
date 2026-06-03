<?php

use App\Models\PageSection;
use Illuminate\Support\Facades\Artisan;

test('page sections install creates home html sections for all locales', function () {
    Artisan::call('page-sections:install', ['--page' => 'home', '--force' => true]);

    foreach (['de', 'en', 'fr', 'it'] as $locale) {
        $hero = PageSection::query()
            ->where('page', 'home')
            ->where('section_key', 'hero')
            ->where('locale', $locale)
            ->first();

        expect($hero)->not->toBeNull()
            ->and($hero->content)->toContain('<section')
            ->and($hero->is_visible)->toBeTrue();
    }
});
