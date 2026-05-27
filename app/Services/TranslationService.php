<?php

namespace App\Services;

use App\Models\TranslationString;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Lang;

class TranslationService
{
    public const CACHE_PREFIX = 'translations.payload_v1';

    public function __construct(
        protected SettingsService $settings
    ) {}

    /**
     * @return list<string>
     */
    public static function platformLocales(): array
    {
        return array_keys(config('creator.platform_locales', config('creator.hub_locales', ['de' => [], 'en' => []])));
    }

    public static function tenantDefaultLocale(): string
    {
        $locale = (string) config('creator.tenant_default_locale', 'de');
        $allowed = self::platformLocales();

        return in_array($locale, $allowed, true) ? $locale : 'de';
    }

    public function text(string $key, ?string $locale = null, ?string $default = null): string
    {
        $locale = $locale ?? app()->getLocale();
        $stored = $this->storedValue($locale, $key);
        if ($stored !== null && $stored !== '') {
            return $stored;
        }

        if ($default !== null) {
            return $default;
        }

        $langValue = $this->langBrandingValue($key, $locale);
        if ($langValue !== null && $langValue !== '') {
            return $langValue;
        }

        $tenantDefault = self::tenantDefaultLocale();
        if ($locale !== $tenantDefault) {
            $langDefault = $this->langBrandingValue($key, $tenantDefault);
            if ($langDefault !== null && $langDefault !== '') {
                return $langDefault;
            }
        }

        return '';
    }

    public function html(string $key, ?string $locale = null): string
    {
        return $this->text($key, $locale);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function list(string $key, ?string $locale = null): array
    {
        $locale = $locale ?? app()->getLocale();
        $raw = $this->storedValue($locale, $key);

        if ($raw === null || $raw === '') {
            $fallback = Lang::get('branding.'.$key, [], $locale);
            if (is_array($fallback)) {
                /** @var list<array<string, mixed>> $fallback */
                return array_values(array_filter($fallback, 'is_array'));
            }

            if ($locale !== self::tenantDefaultLocale()) {
                $fallback = Lang::get('branding.'.$key, [], self::tenantDefaultLocale());
                if (is_array($fallback)) {
                    /** @var list<array<string, mixed>> $fallback */
                    return array_values(array_filter($fallback, 'is_array'));
                }
            }
        }

        if (is_string($raw) && $raw !== '') {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                /** @var list<array<string, mixed>> $decoded */
                return array_values(array_filter($decoded, 'is_array'));
            }
        }

        $fallback = Lang::get('branding.'.$key, [], $locale);
        if (is_array($fallback)) {
            /** @var list<array<string, mixed>> $fallback */
            return array_values(array_filter($fallback, 'is_array'));
        }

        return [];
    }

    public function set(string $locale, string $key, ?string $value, string $format = TranslationString::FORMAT_TEXT): void
    {
        $allowed = self::platformLocales();
        if (! in_array($locale, $allowed, true)) {
            return;
        }

        $row = TranslationString::query()->firstOrNew([
            'locale' => $locale,
            'key' => $key,
        ]);

        $newValue = $value ?? '';
        if ($row->exists && (string) $row->value !== $newValue) {
            $row->previous_value = $row->value;
        }

        $row->value = $newValue;
        $row->format = $format;
        $row->updated_by_user_id = Auth::id();
        $row->updated_at = now();
        $row->save();

        $this->flushLocale($locale);
        app(BrandingService::class)->flushPayloadCache();
    }

    public function revert(string $locale, string $key): bool
    {
        $row = TranslationString::query()
            ->where('locale', $locale)
            ->where('key', $key)
            ->first();

        if ($row === null || $row->previous_value === null) {
            return false;
        }

        $current = $row->value;
        $row->value = $row->previous_value;
        $row->previous_value = $current;
        $row->updated_by_user_id = Auth::id();
        $row->updated_at = now();
        $row->save();

        $this->flushLocale($locale);
        app(BrandingService::class)->flushPayloadCache();

        return true;
    }

    public function revertToLangDefault(string $locale, string $key): void
    {
        TranslationString::query()
            ->where('locale', $locale)
            ->where('key', $key)
            ->delete();

        $this->flushLocale($locale);
        app(BrandingService::class)->flushPayloadCache();
    }

    /**
     * @return array<string, string>
     */
    public function bulkForLocale(string $locale): array
    {
        return Cache::rememberForever(
            self::cacheKeyForLocale($locale),
            function () use ($locale): array {
                $rows = TranslationString::query()
                    ->where('locale', $locale)
                    ->pluck('value', 'key');

                /** @var array<string, string> $out */
                $out = [];
                foreach ($rows as $key => $value) {
                    $out[(string) $key] = is_string($value) ? $value : '';
                }

                return $out;
            }
        );
    }

    public function flushAll(): void
    {
        foreach (self::platformLocales() as $locale) {
            $this->flushLocale($locale);
        }
    }

    public function flushLocale(string $locale): void
    {
        Cache::forget(self::cacheKeyForLocale($locale));
    }

    protected static function cacheKeyForLocale(string $locale): string
    {
        return self::CACHE_PREFIX.'_'.str_replace(['/', '\\', ':'], '_', $locale);
    }

    protected function storedValue(string $locale, string $key): ?string
    {
        $bulk = $this->bulkForLocale($locale);
        if (array_key_exists($key, $bulk)) {
            return $bulk[$key];
        }

        if ($locale === self::tenantDefaultLocale()) {
            $legacy = $this->legacySettingsValue($key);
            if ($legacy !== null && $legacy !== '') {
                return $legacy;
            }
        }

        return null;
    }

    protected function legacySettingsValue(string $key): ?string
    {
        $settingsKey = match ($key) {
            'brand_name' => 'branding.brand_name',
            default => str_starts_with($key, 'colors.') ? 'branding.'.$key : 'branding.'.$key,
        };

        if (str_starts_with($key, 'colors.')) {
            return null;
        }

        $stored = $this->settings->getStored($settingsKey);
        if (is_string($stored) && $stored !== '') {
            return $stored;
        }

        return null;
    }

    protected function langBrandingValue(string $key, string $locale): ?string
    {
        $value = Lang::get('branding.'.$key, [], $locale);
        if (is_string($value)) {
            return $value;
        }

        return null;
    }

    /**
     * Flatten branding.php array to dot keys (marketing.headline, …).
     *
     * @param  array<string, mixed>  $data
     * @param  array<string, string>  $out
     */
    public static function flattenBrandingArray(array $data, string $prefix = '', array &$out = []): array
    {
        foreach ($data as $k => $v) {
            $dot = $prefix === '' ? (string) $k : $prefix.'.'.$k;
            if (is_array($v)) {
                if (self::isListArray($v)) {
                    $out[$dot] = json_encode($v, JSON_UNESCAPED_UNICODE);
                } else {
                    self::flattenBrandingArray($v, $dot, $out);
                }
            } elseif (is_string($v) || is_numeric($v)) {
                $out[$dot] = (string) $v;
            }
        }

        return $out;
    }

    /**
     * @param  array<mixed>  $arr
     */
    protected static function isListArray(array $arr): bool
    {
        if ($arr === []) {
            return true;
        }

        return array_keys($arr) === range(0, count($arr) - 1);
    }
}
