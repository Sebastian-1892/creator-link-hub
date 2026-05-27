<?php

namespace App\Console\Commands;

use Database\Seeders\TranslationStringsSeeder;
use Illuminate\Console\Command;

class TranslationsInstallDefaultsCommand extends Command
{
    protected $signature = 'translations:install-defaults
                            {--sync-json : Regenerate lang/fr.json and lang/it.json from seeder data files first}';

    protected $description = 'Seed translation_strings for all platform locales (DE/EN/FR/IT) from lang/branding.php';

    public function handle(): int
    {
        if ($this->option('sync-json')) {
            foreach ([
                'scripts/generate-hub-de-to-it.php',
                'scripts/build-hub-locale-seeds.php',
            ] as $script) {
                $path = base_path($script);
                if (! is_file($path)) {
                    $this->error('Missing script: '.$path);

                    return self::FAILURE;
                }
                require $path;
            }
            $this->info('Regenerated lang/fr.json and lang/it.json.');
        }

        $this->call('db:seed', ['--class' => TranslationStringsSeeder::class, '--force' => true]);
        $this->info('Default branding translations seeded for all platform locales.');

        return self::SUCCESS;
    }
}
