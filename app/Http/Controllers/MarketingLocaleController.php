<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SetMarketingLocale;
use App\Services\TranslationService;
use App\Services\BrandingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MarketingLocaleController extends Controller
{
    public function __invoke(Request $request, string $locale): RedirectResponse
    {
        $allowed = TranslationService::platformLocales();

        abort_unless(in_array($locale, $allowed, true), 404);

        $request->session()->put(SetMarketingLocale::SESSION_KEY, $locale);

        app(BrandingService::class)->flushPayloadCache();

        return redirect()
            ->back()
            ->withCookie(cookie(
                SetMarketingLocale::COOKIE_NAME,
                $locale,
                60 * 24 * 365,
                '/',
                null,
                (bool) config('session.secure'),
                true,
                false,
                'lax'
            ));
    }
}
