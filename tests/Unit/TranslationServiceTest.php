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

test('tenant default locale reads stored translation strings', function () {
    Cache::flush();

    TranslationString::query()->create([
        'locale' => 'de',
        'key' => 'marketing.eyebrow',
        'value' => 'Nur Deutsch',
        'format' => 'text',
        'updated_at' => now(),
    ]);

    app(TranslationService::class)->flushLocale('de');

    expect(app(TranslationService::class)->text('marketing.eyebrow', 'de'))->toBe('Nur Deutsch');
});

test('non-default locale does not inherit german translation_strings row', function () {
    Cache::flush();

    TranslationString::query()->create([
        'locale' => 'de',
        'key' => 'marketing.headline',
        'value' => 'Nur auf Deutsch in der DB',
        'format' => 'text',
        'updated_at' => now(),
    ]);

    app(TranslationService::class)->flushAll();

    expect(app(TranslationService::class)->text('marketing.headline', 'en'))
        ->toBe('One link. Every channel. More reach.');
    expect(app(TranslationService::class)->text('marketing.headline', 'fr'))
        ->toBe('Un lien. Tous les canaux. Plus de portée.');
    expect(app(TranslationService::class)->text('marketing.features_heading', 'fr'))
        ->toBe('Pourquoi :name ?');
    expect(app(TranslationService::class)->text('marketing.all_templates_link', 'fr'))
        ->toBe('Tous les modèles dans le tableau de bord');
});

test('english locale uses lang file not german legacy settings', function () {
    Cache::flush();
    app(\App\Services\SettingsService::class)->flushCache();

    \App\Models\Setting::query()->updateOrInsert(
        ['key' => 'branding.marketing.headline'],
        ['value' => 'Deutsche Headline aus Settings', 'is_encrypted' => false, 'updated_at' => now()]
    );

    app(TranslationService::class)->flushAll();

    expect(app(TranslationService::class)->text('marketing.headline', 'en'))
        ->toBe('One link. Every channel. More reach.');
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
