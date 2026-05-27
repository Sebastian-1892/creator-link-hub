<?php

namespace Database\Seeders;

use App\Models\TranslationString;
use App\Services\BrandingService;
use App\Services\SettingsService;
use App\Services\TranslationService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Lang;

class TranslationStringsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = app(SettingsService::class);
        $locales = TranslationService::platformLocales();

        foreach ($locales as $locale) {
            $previousLocale = App::getLocale();
            App::setLocale($locale);

            $branding = Lang::get('branding');
            App::setLocale($previousLocale);

            if (! is_array($branding)) {
                $branding = Lang::get('branding', [], $locale);
            }

            $flat = TranslationService::flattenBrandingArray(is_array($branding) ? $branding : []);

            foreach ($flat as $key => $value) {
                if ($value === '') {
                    continue;
                }

                TranslationString::query()->updateOrCreate(
                    ['locale' => $locale, 'key' => $key],
                    [
                        'value' => $value,
                        'format' => str_contains($key, '_html') ? TranslationString::FORMAT_MARKDOWN : TranslationString::FORMAT_TEXT,
                        'updated_at' => now(),
                    ]
                );
            }
        }

        $tenantDefault = TranslationService::tenantDefaultLocale();
        foreach (BrandingService::allSettingKeys() as $settingsKey) {
            if (! str_starts_with($settingsKey, 'branding.')) {
                continue;
            }
            if (str_starts_with($settingsKey, 'branding.colors.') || $settingsKey === 'branding.brand_logo_path') {
                continue;
            }

            $stored = $settings->getStored($settingsKey);
            if (! is_string($stored) || $stored === '') {
                continue;
            }

            $key = str_replace('branding.', '', $settingsKey);
            if ($key === 'brand_name') {
                $key = 'brand_name';
            }

            TranslationString::query()->updateOrCreate(
                ['locale' => $tenantDefault, 'key' => $key],
                [
                    'value' => $stored,
                    'format' => str_contains($key, '_html') ? TranslationString::FORMAT_MARKDOWN : TranslationString::FORMAT_TEXT,
                    'updated_at' => now(),
                ]
            );
        }

        app(TranslationService::class)->flushAll();
        app(BrandingService::class)->flushPayloadCache();
    }
}
