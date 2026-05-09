<?php

namespace App\Support;

use App\Services\ApplicationUpdateService;

/**
 * Kürzt die stdout/stderr-Ausgabe von {@see ApplicationUpdateService}.
 * Lange Filament-Asset-Listen würden sonst die letzten ~3k Zeichen füllen und echte
 * Fehler (z. B. npm EACCES am Ende) in der Benachrichtigung verstecken.
 */
final class UpdateScriptOutputFormatter
{
    private const CHUNK = 5000;

    public static function forNotification(string $output, bool $failed): string
    {
        $output = trim($output);
        $maxChars = $failed ? 14000 : 10000;

        if (mb_strlen($output, 'UTF-8') <= $maxChars) {
            return $output;
        }

        $head = mb_substr($output, 0, self::CHUNK, 'UTF-8');
        $charLen = mb_strlen($output, 'UTF-8');
        $tailStart = max(0, $charLen - self::CHUNK);
        $tail = mb_substr($output, $tailStart, null, 'UTF-8');

        return $head."\n\n".__('filament_git_update.output_truncated_middle')."\n\n".$tail;
    }
}
