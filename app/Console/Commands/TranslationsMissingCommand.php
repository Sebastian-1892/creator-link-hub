<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Finder\Finder;

class TranslationsMissingCommand extends Command
{
    /** Semicolon-separated — commas break Symfony/Laravel default option parsing. */
    private const DEFAULT_SCAN_PATHS = 'resources/views/livewire;resources/views/dashboard.blade.php;resources/views/public;resources/views/components;app/Livewire;app/Http/Controllers/ProfileAvatarController.php;app/Http/Controllers/ProfileDesignImageController.php;resources/views/livewire/pages/auth';

    protected $signature = 'translations:missing
                            {--paths= : Semicolon-separated paths to scan (see DEFAULT_SCAN_PATHS)}
                            {--locale=* : Locales to check (default: de,en)}';

    protected $description = 'List translation keys used in Hub UI that are missing from lang files';

    public function handle(): int
    {
        $locales = $this->option('locale') ?: ['de', 'en'];
        $pathsOption = (string) ($this->option('paths') ?: self::DEFAULT_SCAN_PATHS);
        $paths = array_values(array_filter(array_map('trim', explode(';', $pathsOption))));

        $usedKeys = $this->collectUsedKeys($paths);
        $missing = [];

        $enJson = $this->loadJson('en');
        $deJson = $this->loadJson('de');

        foreach ($usedKeys as $key) {
            if ($this->isNamespacedTranslationKey($key)) {
                foreach ($locales as $locale) {
                    if (! trans()->has($key, $locale)) {
                        $missing[] = ['key' => $key, 'locale' => $locale];
                    }
                }

                continue;
            }

            if (! array_key_exists($key, $enJson)) {
                $missing[] = ['key' => $key, 'locale' => 'en'];
            }

            if (! array_key_exists($key, $deJson) && ! array_key_exists($key, $enJson)) {
                $missing[] = ['key' => $key, 'locale' => 'de'];
            }
        }

        if ($missing === []) {
            $this->info('All '.count($usedKeys).' string keys are present for: '.implode(', ', $locales));

            return self::SUCCESS;
        }

        $this->error(count($missing).' missing translation(s):');
        $this->table(['Key', 'Locale'], array_map(fn ($r) => [$r['key'], $r['locale']], $missing));

        return self::FAILURE;
    }

    /**
     * @param  list<string>  $paths
     * @return list<string>
     */
    protected function collectUsedKeys(array $paths): array
    {
        $keys = [];
        $base = base_path();

        foreach ($paths as $relative) {
            $full = $base.'/'.$relative;
            if (is_file($full)) {
                $this->extractFromFile($full, $keys);

                continue;
            }
            if (! is_dir($full)) {
                continue;
            }
            $finder = Finder::create()->files()->in($full)->name('*.php');
            foreach ($finder as $file) {
                if (str_contains($file->getPathname(), 'Filament')) {
                    continue;
                }
                $this->extractFromFile($file->getPathname(), $keys);
            }
        }

        return array_keys($keys);
    }

    /**
     * @param  array<string, true>  $keys
     */
    protected function extractFromFile(string $path, array &$keys): void
    {
        $content = File::get($path);
        if (preg_match_all("/__\(\s*['\"]((?:[^'\"\\\\]|\\\\.)*)['\"]/u", $content, $m)) {
            foreach ($m[1] as $raw) {
                $key = stripcslashes($raw);
                if ($key === '' || preg_match('/^\d+$/', $key) || str_ends_with($key, '.')) {
                    continue;
                }
                $keys[$key] = true;
            }
        }
    }

    protected function isNamespacedTranslationKey(string $key): bool
    {
        return (bool) preg_match('/^[a-z][a-z0-9_]*(?:\.[a-z][a-z0-9_]*)+$/i', $key);
    }

    /**
     * @return array<string, string>
     */
    protected function loadJson(string $locale): array
    {
        $jsonPath = lang_path($locale.'.json');
        if (! is_file($jsonPath)) {
            return [];
        }

        $data = json_decode((string) file_get_contents($jsonPath), true);

        return is_array($data) ? $data : [];
    }
}
