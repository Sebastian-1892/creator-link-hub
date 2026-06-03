<?php

namespace App\Console\Commands;

use App\Models\PageSection;
use App\Services\HtmlSanitizerService;
use App\Services\PageSectionService;
use App\Services\TranslationService;
use App\Support\PageSectionContentBuilder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Lang;

class PageSectionsInstallCommand extends Command
{
    protected $signature = 'page-sections:install {--page=home : Page slug} {--force : Overwrite existing sections}';

    protected $description = 'Install page sections from branding/lang defaults';

    public function handle(
        PageSectionContentBuilder $builder,
        HtmlSanitizerService $sanitizer,
        PageSectionService $pageSections,
    ): int {
        $page = (string) $this->option('page');
        $registry = config("page-sections.pages.{$page}", []);

        if ($registry === []) {
            $this->error("No section registry found for page [{$page}].");

            return self::FAILURE;
        }

        $registerUrl = route('register');
        $pricingUrl = route('pricing');
        $force = (bool) $this->option('force');

        foreach (TranslationService::platformLocales() as $locale) {
            $brandName = (string) (Lang::get('branding.brand_name', [], $locale) ?: config('app.name'));
            $sections = $builder->buildHomeSections($locale, (string) $brandName, $registerUrl, $pricingUrl);

            foreach ($registry as $sectionKey => $definition) {
                if (($definition['render_type'] ?? 'html') !== 'html') {
                    PageSection::query()->updateOrCreate(
                        [
                            'page' => $page,
                            'section_key' => $sectionKey,
                            'locale' => $locale,
                        ],
                        [
                            'content' => null,
                            'sort_order' => (int) ($definition['sort_order'] ?? 0),
                            'is_visible' => true,
                        ]
                    );

                    continue;
                }

                $existing = PageSection::query()
                    ->where('page', $page)
                    ->where('section_key', $sectionKey)
                    ->where('locale', $locale)
                    ->first();

                if ($existing !== null && $existing->content !== null && $existing->content !== '' && ! $force) {
                    continue;
                }

                $raw = $sections[$sectionKey] ?? '';
                $content = $sanitizer->sanitize($raw);

                PageSection::query()->updateOrCreate(
                    [
                        'page' => $page,
                        'section_key' => $sectionKey,
                        'locale' => $locale,
                    ],
                    [
                        'content' => $content,
                        'previous_content' => $existing?->content,
                        'sort_order' => (int) ($definition['sort_order'] ?? 0),
                        'is_visible' => true,
                        'updated_at' => now(),
                    ]
                );
            }

            $pageSections->flushCache($page, $locale);
            $this->info("Installed sections for [{$page}] locale [{$locale}].");
        }

        return self::SUCCESS;
    }
}
