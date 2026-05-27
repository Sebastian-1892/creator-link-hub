<?php

use App\Models\TranslationString;
use App\Services\BrandingService;
use App\Services\TranslationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('branding payload reflects stored translation for active locale', function () {
    TranslationString::query()->create([
        'locale' => 'de',
        'key' => 'marketing.headline',
        'value' => 'Payload Test Headline',
        'format' => 'text',
        'updated_at' => now(),
    ]);

    app(TranslationService::class)->flushLocale('de');
    app(BrandingService::class)->flushPayloadCache();

    app()->setLocale('de');

    expect(branding_payload()['marketing']['headline'])->toBe('Payload Test Headline');
});
