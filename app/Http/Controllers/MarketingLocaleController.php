<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SetMarketingLocale;
use App\Services\TranslationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Cookie;

class MarketingLocaleController extends Controller
{
    public function __invoke(Request $request, string $locale): RedirectResponse
    {
        $allowed = TranslationService::platformLocales();

        abort_unless(in_array($locale, $allowed, true), 404);

        return redirect()
            ->back()
            ->withCookie(Cookie::create(
                SetMarketingLocale::COOKIE_NAME,
                $locale,
                60 * 24 * 365,
                '/',
                null,
                $request->isSecure(),
                true,
                false,
                'lax'
            ));
    }
}
