<?php

namespace App\Services;

use App\Models\PageSection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class PageSectionService
{
    public const CACHE_PREFIX = 'page_sections.v1';

    /**
     * @return Collection<int, PageSection>
     */
    public function sectionsFor(string $page, ?string $locale = null): Collection
    {
        $locale = $locale ?? app()->getLocale();
        $registry = config("page-sections.pages.{$page}", []);

        if ($registry === []) {
            return collect();
        }

        return Cache::remember(
            $this->cacheKey($page, $locale),
            now()->addDay(),
            function () use ($page, $locale, $registry): Collection {
                $rows = PageSection::query()
                    ->where('page', $page)
                    ->where('locale', $locale)
                    ->get()
                    ->keyBy('section_key');

                return collect($registry)
                    ->map(function (array $definition, string $sectionKey) use ($page, $locale, $rows): PageSection {
                        $row = $rows->get($sectionKey);

                        if ($row === null) {
                            $row = new PageSection([
                                'page' => $page,
                                'section_key' => $sectionKey,
                                'locale' => $locale,
                                'content' => '',
                                'sort_order' => (int) ($definition['sort_order'] ?? 0),
                                'is_visible' => true,
                            ]);
                        }

                        $row->label = (string) ($definition['label'] ?? $sectionKey);
                        $row->render_type = (string) ($definition['render_type'] ?? 'html');
                        $row->blade_partial = is_string($definition['blade_partial'] ?? null)
                            ? $definition['blade_partial']
                            : null;

                        if ($row->sort_order === 0) {
                            $row->sort_order = (int) ($definition['sort_order'] ?? 0);
                        }

                        return $row;
                    })
                    ->sortBy('sort_order')
                    ->values();
            }
        );
    }

    public function find(string $page, string $sectionKey, ?string $locale = null): ?PageSection
    {
        $locale = $locale ?? app()->getLocale();

        return $this->sectionsFor($page, $locale)
            ->first(fn (PageSection $section): bool => $section->section_key === $sectionKey);
    }

    public function flushCache(string $page, ?string $locale = null): void
    {
        if ($locale !== null) {
            Cache::forget($this->cacheKey($page, $locale));

            return;
        }

        foreach (TranslationService::platformLocales() as $code) {
            Cache::forget($this->cacheKey($page, $code));
        }
    }

    public function cacheKey(string $page, string $locale): string
    {
        return self::CACHE_PREFIX.'.'.$page.'.'.$locale;
    }
}
