<?php

namespace App\Support;

class BotDetector
{
    protected static array $patterns = [
        'bot', 'crawl', 'spider', 'slurp', 'facebookexternalhit',
        'whatsapp', 'telegram', 'preview', 'headless', 'lighthouse',
    ];

    public static function isLikelyBot(?string $userAgent): bool
    {
        if ($userAgent === null || $userAgent === '') {
            return true;
        }

        $ua = strtolower($userAgent);

        foreach (self::$patterns as $p) {
            if (str_contains($ua, $p)) {
                return true;
            }
        }

        return false;
    }
}
