<?php

namespace App\Http\Controllers;

use App\Models\Theme;
use App\Services\PageSectionService;
use Illuminate\View\View;

class MarketingController extends Controller
{
    public function home(PageSectionService $pageSections): View
    {
        $stripSlugs = ['heylink-classic', 'minimal-light', 'modern-glass'];

        $stripThemes = Theme::query()
            ->whereIn('slug', $stripSlugs)
            ->get()
            ->sortBy(function (Theme $t) use ($stripSlugs): int {
                $i = array_search($t->slug, $stripSlugs, true);

                return $i !== false ? $i : 99;
            })
            ->values();

        if ($stripThemes->count() < 3) {
            $stripThemes = Theme::query()->orderBy('name')->take(3)->get();
        }

        return view('marketing.home', [
            'pageSections' => $pageSections->sectionsFor('home'),
            'stripThemes' => $stripThemes,
            'carouselThemes' => Theme::query()->orderBy('name')->take(6)->get(),
        ]);
    }

    public function pricing(): View
    {
        return view('marketing.pricing');
    }

    public function faq(): View
    {
        return view('marketing.faq');
    }

    public function help(): View
    {
        return view('marketing.help');
    }
}
