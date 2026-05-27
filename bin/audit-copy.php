#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Kurz-Scan Tenant-Copy (lang + Blade-Views, ohne vendor/storage).
 *
 *   php bin/audit-copy.php
 *   php bin/audit-copy.php --fail-on-findings
 */

$root = dirname(__DIR__);
$failOnFindings = in_array('--fail-on-findings', $argv, true);

$scanDirs = [
    $root . '/lang',
    $root . '/resources/views',
];
$extensions = ['php', 'blade.php'];

$patterns = [
    'TODO' => '/\bTODO\b/i',
    'FIXME' => '/\bFIXME\b/i',
    'lorem' => '/\blorem\s+ipsum\b/i',
    'PLACEHOLDER' => '/\bPLACEHOLDER\b/',
    'Platzhalter' => '/Platzhalter/i',
    'coming soon' => '/coming\s+soon/i',
    'Jane Doe' => '/Jane\s+Doe/i',
];

/** @var list<array{file: string, line: int, rule: string, snippet: string}> */
$findings = [];

foreach ($scanDirs as $dir) {
    if (! is_dir($dir)) {
        continue;
    }
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $file) {
        if (! $file->isFile()) {
            continue;
        }
        $path = $file->getPathname();
        $name = $file->getFilename();
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if ($ext === 'php' && str_ends_with($name, '.blade.php')) {
            // ok
        } elseif (! in_array($ext, ['php'], true)) {
            continue;
        }
        $rel = str_replace($root . '/', '', $path);
        $lines = preg_split('/\r\n|\r|\n/', (string) file_get_contents($path)) ?: [];
        foreach ($lines as $i => $line) {
            foreach ($patterns as $rule => $regex) {
                if (preg_match($regex, $line)) {
                    $findings[] = [
                        'file' => $rel,
                        'line' => $i + 1,
                        'rule' => $rule,
                        'snippet' => mb_substr(trim($line), 0, 120),
                    ];
                }
            }
        }
    }
}

echo 'VPS copy audit — findings: ' . count($findings) . PHP_EOL;
foreach ($findings as $f) {
    echo "  [{$f['rule']}] {$f['file']}:{$f['line']} — {$f['snippet']}" . PHP_EOL;
}

if ($failOnFindings && $findings !== []) {
    exit(1);
}

exit(0);
