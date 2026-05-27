<?php

namespace App\Support;

class LinkPresetHelper
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public static function presets(): array
    {
        return config('link-presets', []);
    }

    public static function preset(?string $key): ?array
    {
        if ($key === null || $key === '') {
            return null;
        }

        $preset = self::presets()[$key] ?? null;

        return is_array($preset) ? $preset : null;
    }

    public static function detectFromUrl(string $url): ?string
    {
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $host = preg_replace('/^www\./', '', $host) ?? $host;

        if ($host === '') {
            return str_starts_with(strtolower($url), 'mailto:') ? 'email' : null;
        }

        $map = [
            'instagram.com' => 'instagram',
            'tiktok.com' => 'tiktok',
            'youtube.com' => 'youtube',
            'youtu.be' => 'youtube',
            'x.com' => 'x',
            'twitter.com' => 'x',
            'open.spotify.com' => 'spotify',
            'spotify.com' => 'spotify',
            'github.com' => 'github',
            'linkedin.com' => 'linkedin',
            'facebook.com' => 'facebook',
            'fb.com' => 'facebook',
            'threads.net' => 'threads',
            'wa.me' => 'whatsapp',
            'api.whatsapp.com' => 'whatsapp',
        ];

        foreach ($map as $domain => $key) {
            if ($host === $domain || str_ends_with($host, '.'.$domain)) {
                return $key;
            }
        }

        return null;
    }

    public static function iconName(?string $presetKey, string $url): string
    {
        $preset = self::preset($presetKey);

        if ($preset !== null) {
            return (string) ($preset['icon'] ?? $presetKey ?? 'link');
        }

        $detected = self::detectFromUrl($url);
        if ($detected !== null) {
            $preset = self::preset($detected);

            return (string) ($preset['icon'] ?? $detected);
        }

        return 'link';
    }

    public static function brandColor(?string $presetKey, string $url): string
    {
        $key = $presetKey ?? self::detectFromUrl($url);
        $preset = self::preset($key);

        return is_array($preset) ? (string) ($preset['color'] ?? '#6366f1') : '#6366f1';
    }
}
