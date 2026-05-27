<?php

use App\Models\TranslationString;
use App\Services\BrandingService;
use App\Services\TranslationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

test('text returns stored translation for locale', function () {
    Cache::flush();
    app(BrandingService::class)->flushPayloadCache();

    TranslationString::query()->create([
        'locale' => 'de',
        'key' => 'marketing.headline',
        'value' => 'DB Headline DE',
        'format' => 'text',
        'updated_at' => now(),
    ]);

    app(TranslationService::class)->flushLocale('de');

    expect(app(TranslationService::class)->text('marketing.headline', 'de'))->toBe('DB Headline DE');
});

test('text falls back to tenant default locale', function () {
    Cache::flush();

    TranslationString::query()->create([
        'locale' => 'de',
        'key' => 'marketing.eyebrow',
        'value' => 'Nur Deutsch',
        'format' => 'text',
        'updated_at' => now(),
    ]);

    app(TranslationService::class)->flushLocale('de');
    app(TranslationService::class)->flushLocale('en');

    expect(app(TranslationService::class)->text('marketing.eyebrow', 'en'))->toBe('Nur Deutsch');
});

test('revert swaps value and previous_value', function () {
    Cache::flush();

    TranslationString::query()->create([
        'locale' => 'de',
        'key' => 'marketing.subline',
        'value' => 'Neu',
        'previous_value' => 'Alt',
        'format' => 'text',
        'updated_at' => now(),
    ]);

    app(TranslationService::class)->flushLocale('de');

    expect(app(TranslationService::class)->revert('de', 'marketing.subline'))->toBeTrue();
    expect(TranslationString::query()->where('key', 'marketing.subline')->value('value'))->toBe('Alt');
});
